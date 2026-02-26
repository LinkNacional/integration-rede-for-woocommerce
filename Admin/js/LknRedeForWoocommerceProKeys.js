jQuery(document).ready(function($) {
    'use strict';
    
    let observer;
    let timeoutId;
    let buttonsAlreadyCreated = false; // Variável para controlar se os botões já foram criados
    const OBSERVER_DURATION = 20000; // 20 segundos
    
    // Função para criar os botões de regeneração de chaves
    function createRegenerateButtons() {
        // Verificar se os botões já foram criados
        if (buttonsAlreadyCreated || $('.lkn-regenerate-keys-btn').length > 0) {
            return true;
        }
        
        const publicKeyField = $('#woocommerce_rede_google_pay_google_pay_public_key');
        const privateKeyField = $('#woocommerce_rede_google_pay_google_pay_private_key');
        
        if (publicKeyField.length && privateKeyField.length) {
            // Desabilitar os campos
            publicKeyField.prop('readonly', true);
            privateKeyField.prop('readonly', true);
            
            // Criar botões de regeneração
            const regenerateBtn1 = createRegenerateButton('public');
            const regenerateBtn2 = createRegenerateButton('private');
            
            // Inserir botões ao lado dos campos
            publicKeyField.after(regenerateBtn1);
            privateKeyField.after(regenerateBtn2);
            
            // Adicionar eventos de clique
            $('.lkn-regenerate-keys-btn').on('click', handleRegenerateKeys);
            $('.lkn-copy-key-btn').on('click', handleCopyKey);
            
            // Marcar que os botões foram criados
            buttonsAlreadyCreated = true;
            
            return true;
        }
        return false;
    }
    
    // Função para criar um botão de regeneração
    function createRegenerateButton(type) {
        const fieldId = type === 'public' ? 'woocommerce_rede_google_pay_google_pay_public_key' : 'woocommerce_rede_google_pay_google_pay_private_key';
        
        // Botão de copiar
        // <button type="button" class="button lkn-copy-key-btn" data-field="${fieldId}" style="margin-right: 5px;">
        //     <span class="dashicons dashicons-clipboard" style="vertical-align: middle;"></span>
        //     <span>Copiar</span>
        // </button>

        return $(`
            <div class="lkn-keys-button-container" style="margin-top: 10px;">
                <button type="button" class="button lkn-regenerate-keys-btn" data-type="${type}">
                    <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                    <span class="btn-text">Gerar Novas Chaves</span>
                </button>
            </div>
        `);
    }
    
    // Função para lidar com o clique de regeneração
    function handleRegenerateKeys(e) {
        e.preventDefault();
        
        const buttons = $('.lkn-regenerate-keys-btn');
        
        // Desabilitar todos os botões e mostrar loading
        buttons.prop('disabled', true);
        buttons.find('.btn-text').text('Gerando...');
        buttons.find('.dashicons').addClass('spin');
        
        // Fazer requisição AJAX
        $.ajax({
            url: lkn_keys_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'lkn_generate_new_google_pay_keys',
                nonce: lkn_keys_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Atualizar os campos com as novas chaves
                    $('#woocommerce_rede_google_pay_google_pay_public_key').val(response.data.public_key);
                    $('#woocommerce_rede_google_pay_google_pay_private_key').val(response.data.private_key);
                    
                    // Mostrar mensagem de sucesso
                    showNotice('Novas chaves geradas com sucesso!', 'success', 'both');
                } else {
                    showNotice(response.data || 'Erro ao gerar novas chaves.', 'error', 'both');
                }
            },
            error: function() {
                showNotice('Erro na comunicação com o servidor.', 'error', 'both');
            },
            complete: function() {
                // Reabilitar botões
                buttons.prop('disabled', false);
                buttons.find('.btn-text').text('Gerar Novas Chaves');
                buttons.find('.dashicons').removeClass('spin');
            }
        });
    }
    
    // Função para lidar com o clique de cópia
    function handleCopyKey(e) {
        e.preventDefault();
        
        const button = $(this);
        const fieldId = button.data('field');
        const field = $('#' + fieldId);
        const value = field.val();
        
        if (!value) {
            showNotice('Nenhuma chave para copiar.', 'warning', button);
            return;
        }
        
        // Tentar copiar usando a API moderna
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(function() {
                showCopySuccess(button);
            }).catch(function() {
                fallbackCopyTextToClipboard(value, button);
            });
        } else {
            // Fallback para navegadores mais antigos
            fallbackCopyTextToClipboard(value, button);
        }
    }
    
    // Função fallback para copiar texto
    function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button);
            } else {
                showNotice('Erro ao copiar a chave.', 'error', button);
            }
        } catch (err) {
            showNotice('Erro ao copiar a chave.', 'error', button);
        }
        
        document.body.removeChild(textArea);
    }
    
    // Função para mostrar sucesso na cópia
    function showCopySuccess(button) {
        const originalText = button.find('span:last').text();
        button.find('span:last').text('Copiado!');
        button.addClass('button-primary');
        
        setTimeout(function() {
            button.find('span:last').text(originalText);
            button.removeClass('button-primary');
        }, 2000);
        
        showNotice('Chave copiada com sucesso!', 'success', button);
    }
    
    // Função para mostrar notificações
    function showNotice(message, type, context) {
        const notice = $(`
            <div class="notice notice-${type} is-dismissible" style="margin: 10px 0;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        `);
        
        // Determinar onde inserir a notificação baseado no contexto
        let targetElement;
        
        if (context === 'both') {
            // Para gerar novas chaves - mostrar em ambos os containers
            $('.lkn-keys-button-container').each(function() {
                const containerNotice = notice.clone(true);
                $(this).prepend(containerNotice);
                
                // Auto remover após 5 segundos
                setTimeout(function() {
                    containerNotice.fadeOut();
                }, 5000);
                
                // Permitir fechar manualmente
                containerNotice.on('click', '.notice-dismiss', function() {
                    containerNotice.fadeOut();
                });
            });
        } else if (context && context.length) {
            // Para contexto específico (botão de copiar)
            targetElement = context.closest('.lkn-keys-button-container');
            targetElement.prepend(notice);
            
            // Auto remover após 5 segundos
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
            
            // Permitir fechar manualmente
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut();
            });
        } else {
            // Fallback para o comportamento antigo
            targetElement = $('.lkn-regenerate-keys-btn').first().parent();
            targetElement.prepend(notice);
            
            // Auto remover após 5 segundos
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
            
            // Permitir fechar manualmente
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut();
            });
        }
    }
    
    // Configurar o observer
    function startObserver() {
        observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const target = $(node).find('.lkn-rede-field-body');
                        if (target.length > 0 || $(node).hasClass('lkn-rede-field-body')) {
                            // Encontrou o elemento, parar o observer e aplicar efeito
                            stopObserver();
                            createRegenerateButtons();
                        }
                    }
                });
            });
        });
        
        // Iniciar observação no body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Definir timeout de 20 segundos
        timeoutId = setTimeout(function() {
            stopObserver();
        }, OBSERVER_DURATION);
    }
    
    // Parar o observer
    function stopObserver() {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
    }
    
    // Verificar se já existe o elemento na página
    if ($('.lkn-rede-field-body').length > 0) {
        createRegenerateButtons();
    } else {
        // Iniciar o observer
        startObserver();
    }
    
    // CSS para animação de spinner
    $('<style>').prop('type', 'text/css').html(`
        .dashicons.spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `).appendTo('head');
});