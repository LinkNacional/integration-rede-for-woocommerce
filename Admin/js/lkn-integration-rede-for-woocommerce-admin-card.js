(function ($) {
  function lknFindGetParameter(parameterName) {
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

  $(window).on('load', function () {
    const adminPage = lknFindGetParameter('section')
    const pluginPages = [
      'maxipago_credit',
      'maxipago_debit',
      'maxipago_pix',
      'rede_credit',
      'rede_debit',
      'rede_pix',
      'integration_rede_pix'
    ]

    if (adminPage && pluginPages.includes(adminPage)) {
      const wcForm = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsLayoutDiv')
      const noticeDiv = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsNoticeDiv')
      const cardDiv = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsCard')
      const formTables = wcForm ? wcForm.querySelectorAll('.form-table') : []

      if (!wcForm) {
        return
      }
      if (!noticeDiv) {
        return
      }
      if (!cardDiv) {
        return
      }
      if (formTables.length < 2) {
        return
      }

      const secondFormTable = formTables[1]

      wcForm.appendChild(noticeDiv)

      if (window.innerWidth <= 1205) {
        wcForm.appendChild(cardDiv)
      } else {
        secondFormTable.id = 'lknIntegrationRedeForWoocommerceSettingsCardTable'
        secondFormTable.appendChild(cardDiv)
      }

      cardDiv.style.display = 'flex'

      const adjustCardDivPosition = () => {
        if (window.innerWidth <= 1205) {
          wcForm.appendChild(cardDiv)
        } else {
          secondFormTable.id = 'lknIntegrationRedeForWoocommerceSettingsCardTable'
          secondFormTable.appendChild(cardDiv)
        }
      }

      window.addEventListener('resize', adjustCardDivPosition)
    }
  })
})(jQuery)
