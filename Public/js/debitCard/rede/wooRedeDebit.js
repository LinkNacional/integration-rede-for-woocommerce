window.jQuery(function ($) {
  // Verifica se janela foi carregada antes da criação do card
  $(window).on('load', lknRedeDebitCardRender)
  // Cria o card somente quando a requisição for concluida
  $(document).on('updated_checkout', lknRedeDebitCardRender)
  $(document).on('wc-fragment-refreshed', lknRedeDebitCardRender)
  $(document).on('woocommerce_cart_updated', lknRedeDebitCardRender)
  $(document).on('woocommerce_checkout_update_order_review', lknRedeDebitCardRender)
  // Fallback para quando o evento não é disparado
  lknRedeDebitCardRender()

  function lknRedeDebitCardRender() {
    if (!document.querySelector('.wc-block-checkout')) {
      // Cria o card somente quando a requisição for concluida
      let $form = $('.woocommerce .woocommerce-checkout')
      if ($form.length === 0) {
        $form = $('#order_review')
      }
      const selectedPaymentMethod = $form.find('input[name="payment_method"]:checked')
      const inputDebitNumber = $form.find('#rede-card-number')
      if (selectedPaymentMethod && selectedPaymentMethod.val() !== 'rede_debit' && !inputDebitNumber) {
        return
      }
      const inputSelectors = {
        numberInput: '#rede-debit-card-number',
        nameInput: '#rede-debit-card-holder-name',
        expiryInput: '#rede-debit-card-expiry',
        cvcInput: '#rede-debit-card-cvc'
      }

      // maybe delete old card data
      $form.data('card', null)

      // init animated card
      $form.card({
        container: '#rede-debit-card-animation',

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
        debug: !!window.wooRede.debug
      })

      // Workaround to maintain the card data rendered after checkout updates
      Object.values(inputSelectors).reverse().forEach(function (selector) {
        $(selector)[0]?.dispatchEvent(new CustomEvent('change'))
      })

      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('focus'))
      $(inputSelectors.numberInput)[0]?.dispatchEvent(new CustomEvent('blur'))
    }
    const paymentBoxP = document.querySelector('.payment_box.payment_method_rede_debit p');
    if (paymentBoxP) {
      paymentBoxP.style.display = 'none';
    }
  }
  
  // Event delegation para capturar mudanças em radios criados dinamicamente
  document.addEventListener('change', function(event) {
    if (event.target.type === 'radio' && event.target.name === 'payment_method') {
      // Só atualiza se o método selecionado for rede_debit
      if (event.target.value === 'rede_debit') {
        updatedCheckout()
      }
    }
  })

  function updatedCheckout(){
    const $container = $('#rede-debit-payment-form');
    if ($container.length) {
      $.post(wooRede.ajaxurl, {
        action: 'rede_debit_refresh_payment_fields',
        nonce: wooRede.nonce,
      }, function (response) {
        if (response.success) {
          // Remove todos os <p> filhos diretos do elemento pai antes de substituir
          $container.parent().children('p').remove();
          $container.replaceWith(response.data.html);
          // Depois de reinserir, recria a animação/cart
          lknRedeDebitCardRender();
        }
      });
    }
  }
})
