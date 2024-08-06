window.jQuery(function ($) {
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (!document.querySelector('#cfw')) {
        jQuery('.blockUI').hide();
    }
  })
})
