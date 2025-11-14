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

  function lknRedeCreditCardRender() {
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
        debug: Boolean(wooRedeVars.debug)
      })

      // Workaround to maintain the card data rendered after checkout updates
      Object.values(inputSelectors).reverse().forEach(function (selector) {
        $(selector)[0]?.dispatchEvent(new CustomEvent('change'))
      })

      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('focus'))
      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('blur'))
    }
    const paymentBoxP = document.querySelector('.payment_box.payment_method_rede_credit p');
    if (paymentBoxP) {
      paymentBoxP.style.display = 'none';
    }
  }

  // Event delegation para capturar mudanças em radios criados dinamicamente
  document.addEventListener('change', function(event) {
    if (event.target.type === 'radio' && event.target.name === 'payment_method') {
      // Só atualiza se o método selecionado for rede_credit
      if (event.target.value === 'rede_credit') {
        updatedCheckout()
      }
    }
  })

  function updatedCheckout(){
    const $container = $('#rede-credit-payment-form');
    if ($container.length) {
      $.post(wooRedeVars.ajaxurl, {
        action: 'rede_refresh_payment_fields',
        nonce: wooRedeVars.nonce,
      }, function (response) {
        if (response.success) {
          // Remove todos os <p> filhos diretos do elemento pai antes de substituir
          $container.parent().children('p').remove();
          $container.replaceWith(response.data.html);
          // Depois de reinserir, recria a animação/cart
          lknRedeCreditCardRender();
        }
      });
    }
  }
})