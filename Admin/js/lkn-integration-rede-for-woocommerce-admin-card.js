(function ($) {
  function lknFindGetParameter (parameterName) {
    let result = null
    let tmp = []
    location.search
      .substr(1)
      .split('&')
      .forEach(function (item) {
        tmp = item.split('=')
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1])
      })
    return result
  }

  $(window).load(function () {
    const adminPage = lknFindGetParameter('section')
    const pluginPages = [
      'maxipago_credit',
      'maxipago_debit',
      'maxipago_pix',
      'rede_credit',
      'rede_debit',
      'rede_pix',
    ]
    if (adminPage && pluginPages.includes(adminPage)) {
      const wcForm = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsLayoutDiv');
      const cardDiv = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsCard');
      const secondFormTable = wcForm.querySelectorAll('.form-table')[1];
      if (secondFormTable && cardDiv) {
        secondFormTable.id = 'lknIntegrationRedeForWoocommerceSettingsCardTable';        
        secondFormTable.appendChild(cardDiv);
        cardDiv.style.display = 'flex';
      }
    }
  })
})(jQuery)
