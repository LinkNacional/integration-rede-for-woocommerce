(function ($) {
  'use strict'

  $(window).on('load', function () {
    let firstRequest = true
    let time = 30
    let attempt = 5
    let activeButton = true

    const formatter = new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    })

    const apiUrl = wpApiSettings.root + 'redeIntegration/verifyPixRedeStatus'

    $(document).ready(function ($) {
      const donationId = $('#donationId').val()

      async function checkPaymentStatus() {
        try {
          if (attempt !== 0) {
            attempt -= 1
          }
          $('.schedule_text').text(phpVarsPix.nextVerify + ' ' + attempt + '):')
          const response = await $.ajax({
            url: apiUrl,
            type: 'GET',
            headers: {
              Accept: 'application/json'
            },
            data: {
              donationId
            }
          })

          if (response === 'Approved' || response === 'Captured') {
            const checkPayment = $('.payment_check_button')
            const schedule = $('#timer')
            const now = new Date()
            const formattedDate = now.getFullYear() + '/' +
              String(now.getMonth() + 1).padStart(2, '0') + '/' +
              String(now.getDate()).padStart(2, '0') + ' ' +
              String(now.getHours()).padStart(2, '0') + ':' +
              String(now.getMinutes()).padStart(2, '0') + ':' +
              String(now.getSeconds()).padStart(2, '0')

            checkPayment.text(formattedDate)
            checkPayment.prop('disabled', true).css({ 'background-color': '#D9D9D9', cursor: 'not-allowed' }).removeClass('back_hover_button')
            clearInterval(paymentTimer)
            schedule.text(phpVarsPix.successPayment).css('font-size', '20px')
            const clonedSchedule = schedule.clone()
            clonedSchedule.insertAfter('.pix_img')

            $('#copy_container').remove()
            $('.pix_img').remove()
            return true
          }
          return false
        } catch (error) {
          console.error('Error:', error)
          return false
        }
      }

      const paymentTimer = setInterval(function () {
        if (firstRequest) {
          firstRequest = false
          time = 60
          activeButton = true
        }

        time -= 1

        const schedule = $('#timer')
        schedule.text(time + 's')

        if (time === 0) {
          if (activeButton) {
            const checkPayment = $('.payment_check_button')
            checkPayment.prop('disabled', false).css({ 'background-color': '#3A3A3A', cursor: 'pointer' }).addClass('back_hover_button')
            activeButton = false

            checkPayment.on('click', async function () {
              const now = new Date()

              const formattedDate = now.getFullYear() + '/' +
                String(now.getMonth() + 1).padStart(2, '0') + '/' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0')

              checkPayment.text(formattedDate)
              checkPayment.prop('disabled', true).css({ 'background-color': '#D9D9D9', cursor: 'not-allowed' }).removeClass('back_hover_button')
              const result = await checkPaymentStatus()
              if (attempt !== 0) {
                time = 30
              } else {
                time = 0
                clearInterval(paymentTimer)
                if (result === false) {
                  schedule.text('MAX')
                }
              }
              if (result === false) {
                setTimeout(function () {
                  checkPayment.prop('disabled', false)
                    .css({
                      'background-color': '#3A3A3A',
                      cursor: 'pointer'
                    }).addClass('back_hover_button')

                  checkPayment.text(phpVarsPix.pixButton)
                }, 7000)
              }
            })
          }
          checkPaymentStatus()
          if (attempt !== 0) {
            time = 30
          } else {
            time = 0
            clearInterval(paymentTimer)
            schedule.text('MAX')
          }
        }
      }, 1000)
    })

    const shareButton = $('.share_button')

    shareButton.on('click', function () {
      const pixLink2 = $('.input_copy_code')
      if (navigator.share) {
        navigator.share({
          title: phpVarsPix.shareTitle,
          text: pixLink2.val()
        })
      } else {
        alert(phpVarsPix.shareError)
      }
    })

    const copyLink = $('.button_copy_code')

    copyLink.on('click', function () {
      const pixLink = $('.input_copy_code')

      navigator.clipboard.writeText(pixLink.val())
      copyLink.text(phpVarsPix.copied)
      copyLink.prop('disabled', true).css({ 'background-color': '#28a428', cursor: 'not-allowed' })

      setTimeout(function () {
        copyLink.prop('disabled', false)
          .css({
            'background-color': '#3A3A3A',
            cursor: 'pointer'
          })

        copyLink.text(phpVarsPix.copy)
      }, 3000)
    })
  })

  // eslint-disable-next-line no-undef
})(jQuery)
