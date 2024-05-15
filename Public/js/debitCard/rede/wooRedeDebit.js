window.jQuery(function ($) {
  if (!document.querySelector('.wc-block-checkout')) {
    // Cria o card somente quando a requisição for concluida
    let $form = $('.woocommerce .woocommerce-checkout')
    if ($form.length == 0) {
      $form = $('#order_review')
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
      $(selector)[0].dispatchEvent(new CustomEvent('change'))
    })

    $(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('focus'))
    $(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('blur'))

  }
})

