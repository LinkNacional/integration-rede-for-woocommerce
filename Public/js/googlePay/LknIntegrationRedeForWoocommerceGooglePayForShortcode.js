/**
 * Configurações globais e utilitários
 */
const settingsGooglePay = window.wc?.wcSettings?.getSetting('rede_google_pay_data', {}) || window.redeGooglePayConfig || {};
const labelGooglePay = window.wp?.htmlEntities?.decodeEntities(settingsGooglePay.title || 'Google Pay') || 'Google Pay';

/**
 * Retorna a configuração base do método de pagamento do Google Pay
 * Configurado para o método 'DIRECT', permitindo descriptografia no próprio back-end.
 */
function getGooglePayBaseConfig() {
    return {
        type: 'CARD',
        parameters: {
            allowedAuthMethods: ["PAN_ONLY", "CRYPTOGRAM_3DS"],
            allowedCardNetworks: ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"]
        },
        tokenizationSpecification: {
            type: 'DIRECT',
            parameters: {
                protocolVersion: 'ECv2',
                publicKey: settingsGooglePay.google_pay_public_key
            }
        }
    };
}

/**
 * Busca o total do carrinho atualizado via API do WooCommerce
 */
async function getCartTotal() {
    try {
        const response = await fetch('/wp-json/wc/store/v1/cart', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        });
        if (response.ok) {
            const cartData = await response.json();
            if (cartData?.totals?.total_price) {
                return (parseInt(cartData.totals.total_price) / 100).toFixed(2);
            }
        }
    } catch (error) {
        console.warn('Erro ao buscar total via API API, usando fallback:', error);
    }
    // Fallback para as configurações localizadas (ex: via wp_localize_script no PHP)
    return parseFloat(settingsGooglePay.total || 0).toFixed(2);
}

/**
 * Monta o objeto de requisição de pagamento
 */
async function buildPaymentDataRequest(baseCardPaymentMethod) {
    const totalPrice = await getCartTotal();
    
    return {
        apiVersion: 2,
        apiVersionMinor: 0,
        allowedPaymentMethods: [baseCardPaymentMethod],
        transactionInfo: {
            totalPriceStatus: 'FINAL',
            totalPrice: totalPrice,
            currencyCode: 'BRL'
        },
        merchantInfo: {
            merchantName: settingsGooglePay.merchant_name || 'Sua Loja',
            merchantId: settingsGooglePay.google_merchant_id || 'google_merchant'
        }
    };
}

/**
 * ---------------------------------------------------------------------------------
 * 1. LÓGICA DO CHECKOUT CLÁSSICO/SHORTCODE DO WOOCOMMERCE
 * ---------------------------------------------------------------------------------
 */
jQuery(document).ready(function ($) {
    // Função principal de inicialização
    function initializeGooglePay() {
        if ($('#google-pay-button').length === 0) return false; // Aborta se não encontrar o container
        if (typeof google === 'undefined' || !google.payments) {
            // Script do Google Pay deve ser enfileirado via wp_enqueue_script no PHP
            // Não tente carregar dinamicamente aqui
            return false;
        }
        initGooglePayClassic();
        return true;
    }

    // Inicialização no DOM ready
    initializeGooglePay();

    // Eventos específicos do WooCommerce para shortcode checkout
    $(document.body).on('updated_checkout', function() {
        setTimeout(() => initializeGooglePay(), 100);
    });

    $(document.body).on('checkout_error', function() {
        setTimeout(() => initializeGooglePay(), 100);
    });

    // Evento para quando o checkout shortcode é totalmente carregado
    $(document.body).on('checkout_place_order_rede_google_pay', function() {
        return true;
    });

    // Observer para detectar mudanças no DOM (útil para shortcode dinâmico)
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    const googlePayButton = document.getElementById('google-pay-button');
                    if (googlePayButton && !googlePayButton.querySelector('button')) {
                        setTimeout(() => initializeGooglePay(), 100);
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    function initGooglePayClassic() {
        const baseConfig = getGooglePayBaseConfig();
        const paymentsClient = new google.payments.api.PaymentsClient({
            environment: settingsGooglePay.environment === 'production' ? 'PRODUCTION' : 'TEST'
        });

        paymentsClient.isReadyToPay({
            apiVersion: 2,
            apiVersionMinor: 0,
            allowedPaymentMethods: [baseConfig]
        }).then(response => {
            if (response.result) {
                renderGooglePayButtonClassic(paymentsClient, baseConfig);
            }
        }).catch(console.error);
    }

    function renderGooglePayButtonClassic(paymentsClient, baseConfig) {
        const container = document.getElementById('google-pay-button');
        if (container && !container.querySelector('button')) {
            const button = paymentsClient.createButton({
                onClick: () => handleGooglePayClickClassic(paymentsClient, baseConfig),
                allowedPaymentMethods: [baseConfig],
                buttonLocale: redeGooglePayConfig.license_valid ? 'pt' : 'en',
                buttonType: settingsGooglePay.google_text_button || 'pay'
            });
            container.appendChild(button);
        }
    }

    async function handleGooglePayClickClassic(paymentsClient, baseConfig) {
        try {
            const paymentDataRequest = await buildPaymentDataRequest(baseConfig);
            const paymentData = await paymentsClient.loadPaymentData(paymentDataRequest);
            
            // Extrai dados específicos necessários como strings simples
            const paymentMethodData = paymentData.paymentMethodData;
            const fullToken = JSON.parse(paymentMethodData.tokenizationData.token);
            const cardNetwork = paymentMethodData.info.cardNetwork || 'VISA';
            const cardFundingSource = paymentMethodData.info.cardFundingSource || 'CREDIT';
            
            // Extrai campos específicos do token como strings simples
            const signature = fullToken.signature;
            const signedKey = fullToken.intermediateSigningKey.signedKey;
            const signatureValue = fullToken.intermediateSigningKey.signatures[0];
            const protocolVersion = fullToken.protocolVersion;
            const signedMessage = fullToken.signedMessage;
            
            // SEMPRE extrair apenas o keyValue - signedKey pode ser objeto ou string JSON
            let signedKeyValue = signedKey;
            if (typeof signedKey === 'object' && signedKey.keyValue) {
                signedKeyValue = signedKey.keyValue;
            } else if (typeof signedKey === 'string') {
                try {
                    const parsedKey = JSON.parse(signedKey);
                    if (parsedKey.keyValue) {
                        signedKeyValue = parsedKey.keyValue;
                    }
                } catch(e) {
                    signedKeyValue = signedKey;
                }
            }
            
            // Fragmentar signedMessage em campos individuais
            let encryptedMessage = '';
            let ephemeralPublicKey = '';
            let tag = '';
            
            if (typeof signedMessage === 'object') {
                encryptedMessage = signedMessage.encryptedMessage || '';
                ephemeralPublicKey = signedMessage.ephemeralPublicKey || '';
                tag = signedMessage.tag || '';
            } else if (typeof signedMessage === 'string') {
                try {
                    const parsedMessage = JSON.parse(signedMessage);
                    encryptedMessage = parsedMessage.encryptedMessage || '';
                    ephemeralPublicKey = parsedMessage.ephemeralPublicKey || '';
                    tag = parsedMessage.tag || '';
                } catch(e) {
                    console.warn('Erro ao parsear signedMessage:', e);
                }
            }
            
            // Procura por diferentes formas de formulário
            let checkoutForm = $('form[name="checkout"]');
            if (checkoutForm.length === 0) {
                checkoutForm = $('form.checkout, form.woocommerce-checkout');
            }
            
            // Preenche os inputs hidden que já estão no template
            $('#google-pay-signature').val(signature);
            $('#google-pay-signed-key').val(signedKeyValue);
            $('#google-pay-signature-value').val(signatureValue);
            $('#google-pay-protocol-version').val(protocolVersion);
            $('#google-pay-encrypted-message').val(encryptedMessage);
            $('#google-pay-ephemeral-public-key').val(ephemeralPublicKey);
            $('#google-pay-tag').val(tag);
            $('#google-pay-card-network').val(cardNetwork);
            $('#google-pay-funding-source').val(cardFundingSource);
            
            // Submete o formulário (adaptado para shortcode)
            checkoutForm = $('form[name="checkout"]');
            if (checkoutForm.length === 0) {
                checkoutForm = $('form.checkout, form.woocommerce-checkout');
            }
            if (checkoutForm.length > 0) {
                // Seleciona o método de pagamento Google Pay antes de submeter
                $('#payment_method_rede_google_pay').prop('checked', true).trigger('change');
                checkoutForm.submit();
            }
        } catch (error) {
            console.error('Erro no processamento do Google Pay:', error);
            if (error.statusCode !== 'CANCELED') {
                alert('Ocorreu um erro com o Google Pay. Tente novamente.');
            }
        }
    }
});

