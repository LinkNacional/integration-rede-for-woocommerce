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
            // MUDANÇA CRÍTICA: Definido como DIRECT para descriptografar no PHP
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
 * 1. LÓGICA DO CHECKOUT CLÁSSICO DO WOOCOMMERCE
 * ---------------------------------------------------------------------------------
 */
jQuery(document).ready(function ($) {
    if ($('#google-pay-button-container').length === 0) return; // Aborta se não for a página de checkout clássico

    // Script do Google Pay deve ser enfileirado via wp_enqueue_script no PHP
    if (typeof google === 'undefined' || !google.payments) {
        // Não tente carregar dinamicamente aqui
        return;
    }
    initGooglePayClassic();

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
        const container = document.getElementById('google-pay-button-container');
        if (container && !container.querySelector('button')) {
            const button = paymentsClient.createButton({
                onClick: () => handleGooglePayClickClassic(paymentsClient, baseConfig),
                allowedPaymentMethods: [baseConfig],
                buttonType: settingsGooglePay.google_text_button || 'pay'
            });
            container.appendChild(button);
        }
    }

    async function handleGooglePayClickClassic(paymentsClient, baseConfig) {
        try {
            const paymentDataRequest = await buildPaymentDataRequest(baseConfig);
            const paymentData = await paymentsClient.loadPaymentData(paymentDataRequest);
            // Injeta o token no formulário do WooCommerce e submete
            let tokenInput = $('input[name="google_pay_token"]');
            if (tokenInput.length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'google_pay_token',
                    value: JSON.stringify(paymentData)
                }).appendTo('form[name="checkout"]');
            } else {
                tokenInput.val(JSON.stringify(paymentData));
            }
            $('form[name="checkout"]').submit();
        } catch (error) {
            console.error('Erro no processamento do Google Pay:', error);
        }
    }
});

/**
 * ---------------------------------------------------------------------------------
 * 2. LÓGICA DO WOOCOMMERCE BLOCKS (REACT)
 * ---------------------------------------------------------------------------------
 */
const ContentGooglePay = (props) => {
    const [paymentReady, setPaymentReady] = React.useState(false);
    const [googlePayData, setGooglePayData] = React.useState(null);
    const { eventRegistration, emitResponse } = props;
    const { onPaymentSetup } = eventRegistration;
    
    // Inicialização da API do Google
    React.useEffect(() => {
        if (typeof google === 'undefined' || !google.payments) {
            // Script do Google Pay deve ser enfileirado via wp_enqueue_script no PHP
            // Não tente carregar dinamicamente aqui
            return;
        }
        initializeGooglePayBlocks();
    }, []);

    // Hook do WC Blocks que dispara no momento em que o usuário clica em "Finalizar Compra"
    React.useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (googlePayData) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            google_pay_token: JSON.stringify(googlePayData)
                        }
                    }
                };
            }
            
            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'Dados do Google Pay não encontrados. Por favor, clique no botão do Google Pay primeiro.'
            };
        });

        return () => unsubscribe();
    }, [onPaymentSetup, emitResponse.responseTypes, googlePayData]);

    const initializeGooglePayBlocks = () => {
        const baseConfig = getGooglePayBaseConfig();
        const paymentsClient = new google.payments.api.PaymentsClient({
            environment: settingsGooglePay.environment === 'production' ? 'PRODUCTION' : 'TEST'
        });

        paymentsClient.isReadyToPay({
            apiVersion: 2,
            apiVersionMinor: 0,
            allowedPaymentMethods: [baseConfig]
        }).then((response) => {
            if (response.result) {
                setPaymentReady(true);
                renderGooglePayButtonBlocks(paymentsClient, baseConfig);
            }
        }).catch(console.error);
    };

    const renderGooglePayButtonBlocks = (paymentsClient, baseConfig) => {
        const container = document.getElementById('google-pay-button-container-blocks');
        if (container && !container.querySelector('button')) {
            const button = paymentsClient.createButton({
                onClick: () => handleGooglePayClickBlocks(paymentsClient, baseConfig),
                allowedPaymentMethods: [baseConfig],
                buttonType: settingsGooglePay.google_text_button || 'pay'
            });
            container.appendChild(button);
        }
    };

    const handleGooglePayClickBlocks = async (paymentsClient, baseConfig) => {
        try {
            const paymentDataRequest = await buildPaymentDataRequest(baseConfig);
            const paymentData = await paymentsClient.loadPaymentData(paymentDataRequest);
            // Armazena o payload descriptografável no estado do componente
            setGooglePayData(paymentData);
            // Automatiza o clique no botão nativo de "Finalizar Compra" do WC Blocks
            setTimeout(() => {
                const submitButton = document.querySelector('.wc-block-components-checkout-place-order-button');
                if (submitButton && !submitButton.disabled) {
                    submitButton.click();
                }
            }, 100);
        } catch (error) {
            console.error('Erro no processamento do Google Pay via Blocks:', error);
            if(error.statusCode !== 'CANCELED') {
                alert('Ocorreu um erro com o Google Pay. Tente novamente.');
            }
        }
    };

    return React.createElement("div", { className: "lkn-rede-google-pay-payment-fields" },
        React.createElement("div", { className: "lkn-rede-google-pay-description-container" },
            React.createElement("p", { className: "lkn-rede-google-pay-description" }, settingsGooglePay.description),
            React.createElement("svg", {
                id: "logo-rede",
                xmlns: "http://www.w3.org/2000/svg",
                viewBox: "0 0 480.72 156.96",
                style: { left: 'initial', position: 'static', top: 'initial', transform: 'none', verticalAlign: 'initial', height: '20px', maxWidth: '200px' }
            },
                React.createElement("title", null, "logo-rede"),
                React.createElement("path", {
                    className: "cls-1",
                    style: { fill: '#ff7800' },
                    d: "M475.56 98.71h-106c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S400.6 21.4 378.27 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51h-29.89V0H263v60.91h-31.23c-29.94.15-46.61 15.31-48.79 37.8h-52.26c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S161.8 21.4 139.47 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51H57.47c-13.74 0-25.93 4.22-32.64 12.5V62.78H0v87.62c0 5 1.56 6.56 6.4 6.56h12.5c4.68 0 6.4-1.56 6.4-6.56v-34.51c0-26.08 16.4-31.24 33.27-31.24h21.06c5.26 25.88 26.93 38.26 52 38.26h54.48c6.26 15 21.21 22.8 45.17 22.8h14.52c23.74 0 43.73-16.87 43.73-41.7V84.65h28.87c5.26 25.88 26.93 38.26 52 38.26h105.16a5.23 5.23 0 0 0 5.15-5.31v-13.9a5.07 5.07 0 0 0-5.15-4.99zM127.91 45.14h12.34c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.07-8.84 7.88-15.81 22.56-15.81zM263 104.8c0 9.84-7.49 16.87-17.18 16.87h-16.24c-13.12 0-21.71-5.15-21.71-18.12 0-12.65 8.59-18.9 21.71-18.9H263v20.15zm103.71-59.66H379c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.12-8.84 7.9-15.81 22.61-15.81z"
                })
            )
        ),
        React.createElement("div", { id: "google-pay-button-container-blocks" }),
        !paymentReady && React.createElement("p", { style: { fontSize: '12px', color: '#666' } }, "Carregando Google Pay...")
    );
};

// Registra o componente React na API do WooCommerce Blocks
if (typeof window.wc !== 'undefined' && window.wc.wcBlocksRegistry) {
    const BlockGatewayGooglePay = {
        name: 'rede_google_pay',
        label: labelGooglePay,
        content: window.wp.element.createElement(ContentGooglePay),
        edit: window.wp.element.createElement(ContentGooglePay),
        canMakePayment: () => true,
        ariaLabel: labelGooglePay,
        supports: { features: settingsGooglePay.supports || ['products'] }
    };

    window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayGooglePay);
}