window.jQuery(function ($) {
  if (!document.querySelector('.wc-block-checkout')) {
    // Cria o card somente quando a requisição for concluida
    $(document).ajaxComplete(function (event, xhr, settings) {
      function formatarCPF(cpf) {
        cpf = cpf.replace(/\D/g, ''); // Remove caracteres não numéricos
        cpf = cpf.slice(0, 11); // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os primeiros 3 dígitos
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os segundos 3 dígitos
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen após os últimos 3 dígitos
        return cpf;
      }
      $('#maxipagoCreditCardCpf').on('input', function () {
        var input = $(this);
        var cpf = input.val();
        cpf = formatarCPF(cpf);
        input.val(cpf);
      });
      if (xhr.status === 200 && settings.url === '/?wc-ajax=update_order_review') {
        const $form = $('.woocommerce .woocommerce-checkout')
        const inputSelectors = {
          numberInput: '#maxipago-card-number',
          nameInput: '#maxipago-card-holder-name',
          expiryInput: '#maxipago-card-expiry',
          cvcInput: '#maxipago-card-cvc'
        }

        // maybe delete old card data
        $form.data('card', null)

        // init animated card
        $form.card({
          container: '#maxipago-card-animation',

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
          $(selector)[0].dispatchEvent(new CustomEvent('change'))
        })

        $(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('focus'))
        $(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('blur'))
      }
    });

  }
})
