(function ($) {
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
      const wcForm = document.getElementById('mainform')
      // Pega sempre o ultimo form-table para mostraro link

      const noticeDiv = document.createElement('div')
      noticeDiv.setAttribute('style', 'background-color: #fcf9e8;color: #646970;border: solid 1px #d3d3d3;border-left: 4px #dba617 solid;font-size: 16px;margin-top: 10px;')

      noticeDiv.innerHTML = '<a  href="https://cliente.linknacional.com.br/solicitar/wordpress-woo-gratis/" target="_blank" style="text-decoration:none; display: block;padding: 10px;">' + lknPhpVariables.freeHost + '</a>'

      const lknCieloNoticeDiv = document.createElement('div')
      lknCieloNoticeDiv.setAttribute('style', 'padding: 10px 5px;background-color: #fcf9e8;color: #646970;border: solid 1px lightgrey;border-left-color: #dba617;border-left-width: 4px;font-size: 14px;min-width: 625px;margin-top: 10px;')
      lknCieloNoticeDiv.setAttribute('id', 'lkn-cielo-pro-notice')
      if (!document.querySelector(`#woocommerce_${adminPage}_PRO`) && adminPage !== 'maxipago_pix' && adminPage !== 'rede_pix') {
        lknCieloNoticeDiv.innerHTML = '<div style="font-size: 21px;padding: 6px 0px 10px 0px;">' + lknPhpVariables.title + '</div>' +
          '<a href="https://www.linknacional.com.br/wordpress/plugins/" target="_blank">' + lknPhpVariables.desc + '</a>' +
          '<ul style="margin: 10px 28px;list-style: disclosure-closed;">' +
          '<li>' + lknPhpVariables.capture + '</li>' +
          '<li>' + lknPhpVariables.tax + '</li>' +
          '<li>' + lknPhpVariables.css + '</li>' +
          '<li>' + lknPhpVariables.pix + '</li>' +
          '</ul>'
        wcForm.append(lknCieloNoticeDiv)

        const submitButton = wcForm.querySelector('button[type="submit"]').parentElement;
        if (submitButton) {
          submitButton.insertAdjacentHTML('beforebegin', lknIntegrationRedeForWoocommerceProFields(adminPage));
        }
      }

      wcForm.append(noticeDiv)
    }

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

    // Código para deixar o campo de soft descriptor desabilitado caso o campo de habilitar esteja desmarcado
    const enabledSoftDescriptorInput = document.querySelector(`#woocommerce_${adminPage}_enabled_soft_descriptor`)
    const softDescriptorInput = document.querySelector(`#woocommerce_${adminPage}_soft_descriptor`)

    if (enabledSoftDescriptorInput && softDescriptorInput) {      

      if(document.querySelector('#lknIntegrationRedeForWoocommerceSoftDescriptorErrorDiv') && enabledSoftDescriptorInput.checked){
        var newP = document.createElement('p');
        newP.textContent = lknPhpVariables.descriptionError; 
        newP.style.color = 'FF0000'; 

        enabledSoftDescriptorInput.parentElement.appendChild(newP);
      }

      if(!enabledSoftDescriptorInput.checked){
        softDescriptorInput.setAttribute('disabled', 'disabled')
      }

      enabledSoftDescriptorInput.addEventListener('change', function () {
        if (this.checked) {
          softDescriptorInput.removeAttribute('disabled')
        } else {
          softDescriptorInput.setAttribute('disabled', 'disabled')
        }
      })
    }
  })
})(jQuery)
