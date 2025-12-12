(function ($) {
    'use strict';

    $(document).ready(function() {
        // Observer para detectar mudanças no DOM
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Verifica se o campo woocommerce_rede_debit_interest_or_discount foi adicionado
                        const interestOrDiscountField = $(node).find('#woocommerce_rede_debit_interest_or_discount');
                        if (interestOrDiscountField.length > 0 || $(node).is('#woocommerce_rede_debit_interest_or_discount')) {
                            initInstallmentLogic();
                        }
                    }
                });
            });
        });

        // Inicia o observer
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Verifica se o campo já existe na página
        if ($('#woocommerce_rede_debit_interest_or_discount').length > 0) {
            initInstallmentLogic();
        }

        function initInstallmentLogic() {
            const $interestOrDiscountField = $('#woocommerce_rede_debit_interest_or_discount');
            const $installmentInterestCheckbox = $('#woocommerce_rede_debit_installment_interest');
            const $installmentDiscountCheckbox = $('#woocommerce_rede_debit_installment_discount');

            // Função para controlar a exibição dos campos de juros
            function toggleInterestFields() {
                const isInterestSelected = $interestOrDiscountField.val() === 'interest';
                const isInterestCheckboxChecked = $installmentInterestCheckbox.is(':checked');

                if (isInterestSelected) {
                    // Mostra o bloco de interesse e esconde o bloco de desconto
                    $('#woocommerce_rede_debit_installment_interest').closest('tr').show();
                    $('#woocommerce_rede_debit_installment_discount').closest('tr').hide();

                    if (isInterestCheckboxChecked) {
                        // Exibe todos os campos de juros
                        $('#woocommerce_rede_debit_min_interest').closest('fieldset').show();
                        
                        // Exibe todos os campos que seguem o padrão woocommerce_rede_debit_[numero]x
                        $('[id^="woocommerce_rede_debit_"][id$="x"]:not([id*="_discount"])').closest('fieldset').show();
                    } else {
                        // Esconde todos os campos de juros
                        $('#woocommerce_rede_debit_min_interest').closest('fieldset').hide();
                        
                        // Esconde todos os campos que seguem o padrão woocommerce_rede_debit_[numero]x
                        $('[id^="woocommerce_rede_debit_"][id$="x"]:not([id*="_discount"])').closest('fieldset').hide();
                    }
                }
            }

            // Função para controlar a exibição dos campos de desconto
            function toggleDiscountFields() {
                const isDiscountSelected = $interestOrDiscountField.val() === 'discount';
                const isDiscountCheckboxChecked = $installmentDiscountCheckbox.is(':checked');

                if (isDiscountSelected) {
                    // Mostra o bloco de desconto e esconde o bloco de interesse
                    $('#woocommerce_rede_debit_installment_discount').closest('tr').show();
                    $('#woocommerce_rede_debit_installment_interest').closest('tr').hide();

                    if (isDiscountCheckboxChecked) {
                        // Exibe todos os campos de desconto que seguem o padrão woocommerce_rede_debit_[numero]x_discount
                        $('[id^="woocommerce_rede_debit_"][id$="x_discount"]').closest('fieldset').show();
                    } else {
                        // Esconde todos os campos de desconto que seguem o padrão woocommerce_rede_debit_[numero]x_discount
                        $('[id^="woocommerce_rede_debit_"][id$="x_discount"]').closest('fieldset').hide();
                    }
                }
            }

            // Função principal para aplicar toda a lógica
            function applyInstallmentLogic() {
                toggleInterestFields();
                toggleDiscountFields();
            }

            // Event listeners
            $interestOrDiscountField.on('change', function() {
                applyInstallmentLogic();
            });

            $installmentInterestCheckbox.on('change', function() {
                toggleInterestFields();
            });

            $installmentDiscountCheckbox.on('change', function() {
                toggleDiscountFields();
            });

            // Aplica a lógica inicial
            applyInstallmentLogic();
        }
    });

})(jQuery);
