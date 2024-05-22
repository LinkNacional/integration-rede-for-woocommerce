window.jQuery(function ($) {
  if (!document.querySelector('.wc-block-checkout')) {
    // Cria o card somente quando a requisição for concluida
    let verify = true
    setInterval(() => {
      if (verify) {
        function formatarCPF(cpf) {
          cpf = cpf.replace(/\D/g, ''); // Remove caracteres não numéricos
          cpf = cpf.slice(0, 11); // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
          cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os primeiros 3 dígitos
          cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os segundos 3 dígitos
          cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen após os últimos 3 dígitos
          return cpf;
        }
    
        $('#maxipago_debit_card_cpf').on('input', function () {
          var input = $(this);
          var cpf = input.val();
          cpf = formatarCPF(cpf);
          input.val(cpf);
        });
    
    
        let $form = $('.woocommerce .woocommerce-checkout')
        if ($form.length == 0) {
          $form = $('#order_review')
        }
        
        const inputSelectors = {
          numberInput: '#maxipago_debit_card_number',
          nameInput: '#maxipago_debit_card_holder_name',
          expiryInput: '#maxipago_debit_card_expiry',
          cvcInput: '#maxipago_debit_card_cvc'
        }
    
        // maybe delete old card data
        $form.data('card', null)
    
        // init animated card
        $form.card({
          container: '#maxipagoDebitCardAnimation',
    
          /**
           * Selectors
           */
          formSelectors: inputSelectors,
    
          /**
           * Placeholders
           */
          placeholders: {
            number: '•••• •••• •••• ••••',
            name: 'NOME',
            expiry: 'MM/ANO',
            cvc: 'CVC'
          },
    
          /**
           * Translation Brazilian Portuguese
           */
          messages: {
            validDate: 'VALIDADE',
            monthYear: ''
          },
    
          /**
           * Debug
           */
          debug: !!window.wooMaxipago.debug
        })
    
        // Workaround to maintain the card data rendered after checkout updates
        Object.values(inputSelectors).reverse().forEach(function (selector) {
          $(selector)[0]?.dispatchEvent(new CustomEvent('change'))
        })
    
        $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('focus'))
        $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('blur'))
      }
    })
  }
})

