/**
 * Script para dispensar notificação de fraude
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Event listener para botão de dismiss da notificação
    $(document).on('click', '.lkn-fraud-notice-dismiss', function(e) {
        e.preventDefault();
        
        var $notice = $(this).closest('.notice');
        var $button = $(this);
        
        // Disable button para evitar múltiplos cliques
        $button.prop('disabled', true);
        
        // Fazer requisição AJAX
        $.ajax({
            url: lknRedeDismissNotice.ajax_url,
            type: 'POST',
            data: {
                action: lknRedeDismissNotice.action,
                nonce: lknRedeDismissNotice.nonce
            },
            beforeSend: function() {
                // Adicionar spinner ou loading indicator
                $button.html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');
            },
            success: function(response) {
                if (response.success) {
                    // Fade out e remover a notificação
                    $notice.fadeOut('fast', function() {
                        $(this).remove();
                    });
                } else {
                    console.error('Erro ao dispensar notificação:', response.data);
                    // Restaurar botão em caso de erro
                    $button.html('×').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição AJAX:', error);
                // Restaurar botão em caso de erro
                $button.html('×').prop('disabled', false);
            }
        });
    });
});