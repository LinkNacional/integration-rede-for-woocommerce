window.jQuery(function ($) {
  $(document).ajaxComplete(function (event, xhr, settings) {
    jQuery('.blockUI').hide()
  })
})
