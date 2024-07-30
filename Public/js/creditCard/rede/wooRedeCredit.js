window.jQuery(function ($) {
  // Verifica se janela foi carregada antes da criação do card
  $(window).on('load', lknRedeCreditCardRender)
  // Cria o card somente quando a requisição for concluida
  $(document).on('updated_checkout', lknRedeCreditCardRender)
  $(document).on('wc-fragment-refreshed', lknRedeCreditCardRender)
  $(document).on('woocommerce_cart_updated', lknRedeCreditCardRender)
  $(document).on('woocommerce_checkout_update_order_review', lknRedeCreditCardRender)
  // Fallback para quando o evento não é disparado
  lknRedeCreditCardRender()

  function lknRedeCreditCardRender () {
    // Não carrega para formulário em bloco
    if (!document.querySelector('.wc-block-checkout')) {
      // Verifica se é página de fatura ou se é página de checkout
      let $form = $('.woocommerce .woocommerce-checkout')
      if ($form.length === 0) {
        $form = $('#order_review')
      }
      const selectedPaymentMethod = $form.find('input[name="payment_method"]:checked')
      const inputCreditNumber = $form.find('#rede-card-number')
      if (selectedPaymentMethod && selectedPaymentMethod.val() !== 'rede_credit' && !inputCreditNumber) {
        return
      }
      const inputSelectors = {
        numberInput: '#rede-card-number',
        nameInput: '#rede-card-holder-name',
        expiryInput: '#rede-card-expiry',
        cvcInput: '#rede-card-cvc'
      }

      // maybe delete old card data
      $form.data('card', null)

      // init animated card
      $form.card({
        container: '#rede-card-animation',

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
        debug: Boolean(window.wooRede.debug)
      })

      // Workaround to maintain the card data rendered after checkout updates
      Object.values(inputSelectors).reverse().forEach(function (selector) {
        $(selector)[0]?.dispatchEvent(new CustomEvent('change'))
      })

      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('focus'))
      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('blur'))
    }
  }
})
