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
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
          },
          success: function (status) {
            lknWcRedeValidateButton.disabled = false
            location.reload()
          },
          error: function (error) {
            console.error(error)
            let errorMessage = 'Error clearing logs'
            if (error.responseJSON && error.responseJSON.message) {
              errorMessage = error.responseJSON.message
            } else if (error.status === 403) {
              errorMessage = 'You do not have permission to perform this action.'
            } else if (error.status === 401) {
              errorMessage = 'Authentication required. Please refresh the page and try again.'
            }
            alert(errorMessage)
            lknWcRedeValidateButton.disabled = false
            lknWcRedeValidateButton.className = lknWcRedeValidateButton.className.replace(' is-busy', '')
          }
        })
      } else {
        // Usuario cancelou, restaura o botão
        lknWcRedeValidateButton.disabled = false
        lknWcRedeValidateButton.className = lknWcRedeValidateButton.className.replace(' is-busy', '')
      }
    })
  }
})