window.jQuery(function ($) {
  // Verifica se janela foi carregada antes da criação do card
  $(window).on('load', lknMaxipagoCreditCardRender)
  // Cria o card somente quando a requisição for concluida
  $(document).on('updated_checkout', lknMaxipagoCreditCardRender)
  $(document).on('wc-fragment-refreshed', lknMaxipagoCreditCardRender)
  $(document).on('woocommerce_cart_updated', lknMaxipagoCreditCardRender)
  $(document).on('woocommerce_checkout_update_order_review', lknMaxipagoCreditCardRender)
  // Fallback para quando o evento não é disparado
  lknMaxipagoCreditCardRender()

  function lknMaxipagoCreditCardRender () {
    if (!document.querySelector('.wc-block-checkout')) {
      // Cria o card somente quando a requisição for concluida
      function formatarCPF (cpf) {
        cpf = cpf.replace(/\D/g, '') // Remove caracteres não numéricos
        cpf = cpf.slice(0, 11) // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2') // Adiciona ponto após os primeiros 3 dígitos
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2') // Adiciona ponto após os segundos 3 dígitos
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2') // Adiciona hífen após os últimos 3 dígitos
        return cpf
      }
      $('#maxipagoCreditCardCpf').on('input', function () {
        const input = $(this)
        let cpf = input.val()
        cpf = formatarCPF(cpf)
        input.val(cpf)
      })
      let $form = $('.woocommerce .woocommerce-checkout')
      if ($form.length === 0) {
        $form = $('#order_review')
      }
      const selectedPaymentMethod = $form.find('input[name="payment_method"]:checked')
      const inputCreditNumber = $form.find('#maxipago-card-number')
      if (selectedPaymentMethod && selectedPaymentMethod.val() !== 'maxipago_credit' && !inputCreditNumber) {
        return
      }

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
        $(selector)[0]?.dispatchEvent(new CustomEvent('change'))
      })

      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('focus'))
      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('blur'))
    }

    //Remove o cpnj ou cpf do campo que não foi preenchido
    billingCnpjInput = document.querySelector('#billing_cnpj')
    billingCpfInput = document.querySelector('#billing_cpf')
    if (billingCnpjInput && billingCpfInput) {
      billingCnpjInput.addEventListener('change', function () {
        billingCpfInput.value = '';
      });
      billingCpfInput.addEventListener('change', function () {
        billingCnpjInput.value = '';
      });
    }
  }
})
