(function ($) {
  $(document).ready(function () {
    const message = $('<p id="footer-left" class="alignleft"></p>')

    message.html('Se você gosta do plugin <strong>Woo-rede</strong>, deixe-nos uma classificação de <a href="https://wordpress.org/support/plugin/woo-rede/reviews/?filter=5#postform" target="_blank" class="give-rating-link" style="text-decoration:none;" data-rated="Obrigado :)">★★★★★</a>. Leva um minuto e nos ajuda muito. Obrigado antecipadamente!')

    message.css({
      'text-align': 'center',
      'width': '100%',
      padding: '10px 0px',
      'font-size': '13px',
      color: '#666'
    })

    $('#wpbody').append(message)

    $('.give-rating-link').on('click', function (e) {
      $('#footer-left').html('Obrigado :)').css('text-align', 'center')
    })
  })
})(jQuery)
