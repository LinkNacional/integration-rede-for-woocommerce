(function ($) {
  $(document).ready(function () {
    // Expiration count
    const $countInput = $('#woocommerce_integration_rede_pix_expiration_count')

    const countDefaultValue = 24

    $countInput.on('input', function () {
      if ($(this).val() !== countDefaultValue.toString()) {
        $(this).val(countDefaultValue)
      }
    })

    if ($countInput.length) {
      const $countFieldset = $countInput.closest('fieldset')

      $countFieldset.append('<p class="pro-version-info">Disponível no <a target="_blank" href="https://www.linknacional.com.br/wordpress/woocommerce/rede/">PRO</a>.</p>')

      $countFieldset.css({
        display: 'flex',
        'flex-wrap': 'wrap',
        gap: '6px'
      })
    }

    const $shouButton = $('#woocommerce_integration_rede_pix_show_button')

    if ($shouButton.length) {
      const $countFieldset = $shouButton.closest('fieldset')

      $countFieldset.append('<p class="pro-version-info">Disponível no <a target="_blank" href="https://www.linknacional.com.br/wordpress/woocommerce/rede/">PRO</a>.</p>')

      $countFieldset.css({
        display: 'flex',
        'flex-wrap': 'wrap',
        gap: '6px'
      })
    }

    // Select status
    const $selectInput = $('#woocommerce_integration_rede_pix_payment_complete_status')

    const selectDefaultValue = 'processing'

    $selectInput.on('change', function () {
      if ($(this).val() !== selectDefaultValue) {
        $(this).val(selectDefaultValue).trigger('change')
      }
    })

    if ($selectInput.length) {
      const $selectFieldset = $selectInput.closest('fieldset')

      $selectFieldset.append('<p class="pro-version-info">Disponível no <a target="_blank" href="https://www.linknacional.com.br/wordpress/woocommerce/rede/">PRO</a>.</p>')

      $selectFieldset.css({
        display: 'flex',
        'flex-wrap': 'wrap',
        gap: '6px',
        width: '100% !important'
      })
    }

    $(document).ready(function () {
      function applyStyle() {
        $('.select2-container').css('width', 'fit-content')
      }

      const observer = new MutationObserver(function (mutationsList) {
        mutationsList.forEach(function () {
          applyStyle()
        })
      })

      observer.observe(document.body, { childList: true, subtree: true })
    })
  })
})(jQuery)
