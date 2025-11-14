jQuery(document).ready(function($) {
    
    function initShortcodeCheckoutEvents() {
        // Adiciona evento nos radio buttons de payment_method
        $('body').on('change.shortcode_checkout', 'input[name="payment_method"]', function() {
            var selectedMethod = $(this).val();
            
            // Não executa update_checkout para métodos de crédito
            if (selectedMethod === 'rede_credit' || selectedMethod === 'maxipago_credit') {
                return;
            }
            
            $('body').trigger('update_checkout');
        });
    }
    
    // Inicia os eventos diretamente
    initShortcodeCheckoutEvents();
    
    // Re-inicializa após updates do checkout
    $('body').on('updated_checkout', function() {
        initShortcodeCheckoutEvents();
    });
    
});