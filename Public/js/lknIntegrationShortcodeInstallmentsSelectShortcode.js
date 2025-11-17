jQuery(document).ready(function($) {
    
    function initInstallmentSelectEvents() {
        // Remove eventos anteriores para evitar duplicação
        $('body').off('change.installment_shortcode', '.lknIntegrationRedeForWoocommerceSelect');
        
        // Adiciona evento nos selects de parcelas
        $('body').on('change.installment_shortcode', '.lknIntegrationRedeForWoocommerceSelect', function() {
            var $select = $(this);
            var selectedInstallments = $select.val();
            var gatewayMethod = getSelectedPaymentMethod();
            
            // Verificar se é um método de crédito válido
            if (!gatewayMethod || !isValidCreditMethod(gatewayMethod)) {
                return;
            }
            
            // Fazer requisição AJAX para atualizar sessão
            updateInstallmentSession(gatewayMethod, selectedInstallments);
        });
    }
    
    function getSelectedPaymentMethod() {
        var $checkedRadio = $('input[name="payment_method"]:checked');
        return $checkedRadio.length ? $checkedRadio.val() : null;
    }
    
    function isValidCreditMethod(method) {
        return ['rede_credit', 'maxipago_credit'].includes(method);
    }
    
    function updateInstallmentSession(gateway, installments) {
        if (!gateway || !installments) {
            return;
        }
        
        $.ajax({
            url: lknInstallmentShortcodeVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_installment_session',
                nonce: lknInstallmentShortcodeVars.nonce,
                gateway: gateway,
                installments: installments
            },
            beforeSend: function() {
                // Opcional: adicionar loading indicator
            },
            success: function(response) {
                if (response.success) {
                    // Disparar update do checkout para recalcular totais
                    $('body').trigger('update_checkout');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao atualizar parcelas:', error);
            },
            complete: function() {
                // Opcional: remover loading indicator
            }
        });
    }
    
    function waitForCheckoutLoad() {
        var checkInterval = setInterval(function() {
            if ($('.woocommerce-checkout').length > 0 && $('.lknIntegrationRedeForWoocommerceSelect').length > 0) {
                clearInterval(checkInterval);
                initInstallmentSelectEvents();
            }
        }, 100);
        
        // Timeout de segurança (10 segundos)
        setTimeout(function() {
            clearInterval(checkInterval);
            initInstallmentSelectEvents();
        }, 10000);
    }
    
    // Inicializar quando checkout estiver carregado
    waitForCheckoutLoad();
    
    // Re-inicializar após updates do checkout
    $('body').on('updated_checkout', function() {
        initInstallmentSelectEvents();
    });
    
});