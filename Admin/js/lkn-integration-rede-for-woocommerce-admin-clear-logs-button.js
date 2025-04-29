const lknWcRedeUrlParams = new URLSearchParams(window.location.search)
const lknWcRedeSection = lknWcRedeUrlParams.get('section')
// Dom loaded

// Dom carregado

document.addEventListener('DOMContentLoaded', function () { 
  const lknWcRedeValidateButton = document.querySelector(`#woocommerce_${lknWcRedeSection}_clear_order_records`)
  const lknWcRedeShowOrderLogs = document.querySelector(`#woocommerce_${lknWcRedeSection}_show_order_logs`)
  const lknWcRedeDebug = document.querySelector(`#woocommerce_${lknWcRedeSection}_debug`)

  function changeShowOrderLogs() {
    if(!lknWcRedeDebug.checked) {
      lknWcRedeShowOrderLogs.checked = false
      lknWcRedeShowOrderLogs.disabled = true
    }
    else{
      lknWcRedeShowOrderLogs.disabled = false
    }
  }

  if(lknWcRedeDebug && lknWcRedeShowOrderLogs) {
    changeShowOrderLogs()
    lknWcRedeDebug.onchange = () => {
      changeShowOrderLogs()
    }
  }


  const lknWcRedeForValidateButton = document.querySelector(`label[for="woocommerce_${lknWcRedeSection}_clear_order_records"]`)
  if (lknWcRedeForValidateButton) {
    lknWcRedeForValidateButton.removeAttribute('for')
  }

  if (lknWcRedeValidateButton) {
    lknWcRedeValidateButton.value = lknWcRedeTranslations.clearLogs
    lknWcRedeValidateButton.addEventListener('click', function () {
      lknWcRedeValidateButton.disabled = true
      lknWcRedeValidateButton.className = lknWcRedeValidateButton.className + ' is-busy'

      if (confirm(lknWcRedeTranslations.alertText)) {
        jQuery.ajax({
          type: 'DELETE',
          url: wpApiSettings.root + 'redeIntegration/clearOrderLogs',
          contentType: 'application/json',
          success: function (status) {
            lknWcRedeValidateButton.disabled = false
            location.reload()
          },
          error: function (error) {
            console.error(error)
            lknWcRedeValidateButton.disabled = false
            lknWcRedeValidateButton.className = lknWcRedeValidateButton.className.replace(' is-busy', '')
          }
        })
      }
    })
  }
})