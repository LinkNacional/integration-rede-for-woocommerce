jQuery(document).ready(function($) {
    
    function initShortcodeCheckoutEvents() {
        // Remove eventos anteriores para evitar duplicação
        $('body').off('change.shortcode_checkout', 'input[name="payment_method"]');
        
        // Adiciona evento nos radio buttons de payment_method
        $('body').on('change.shortcode_checkout', 'input[name="payment_method"]', function() {
            var selectedMethod = $(this).val();
            
            // Se for método de crédito, atualizar sessão de parcelas para 1
            if (selectedMethod === 'rede_credit' || selectedMethod === 'maxipago_credit') {
                updateInstallmentSessionForCredit(selectedMethod);
            } else {
                $('body').trigger('update_checkout');
            }
        });
    }
    
    function updateInstallmentSessionForCredit(gateway) {
        if (!gateway || !lknInstallmentShortcodeVarsCheckout) {
            $('body').trigger('update_checkout');
            return;
        }
        
        // Pegar o valor atual do select de parcelas
        var currentInstallments = getCurrentInstallmentValue(gateway);
        
        $.ajax({
            url: lknInstallmentShortcodeVarsCheckout.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_installment_session',
                nonce: lknInstallmentShortcodeVarsCheckout.nonce,
                gateway: gateway,
                installments: currentInstallments
            },
            success: function(response) {
                if (response.success) {
                    $('body').trigger('update_checkout');
                } else {
                    // Fallback: trigger update_checkout mesmo se falhar
                    $('body').trigger('update_checkout');
                }
            },
            error: function() {
                // Fallback: trigger update_checkout mesmo se der erro
                $('body').trigger('update_checkout');
            }
        });
    }
    
    function getCurrentInstallmentValue(gateway) {
        var $installmentSelect;
        
        // Buscar pelo ID específico do gateway
        if (gateway === 'rede_credit') {
            $installmentSelect = $('#rede-card-installments');
        } else if (gateway === 'maxipago_credit') {
            $installmentSelect = $('#maxipago-card-installments');
        }

        // Retornar o valor selecionado ou 1 como fallback
        return $installmentSelect && $installmentSelect.length > 0 ? ($installmentSelect.val() || 1) : 1;
    }
    
    // Inicia os eventos diretamente
    initShortcodeCheckoutEvents();
    
    // Re-inicializa após updates do checkout
    $('body').on('updated_checkout', function() {
        initShortcodeCheckoutEvents();
    });
    
});