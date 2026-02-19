(function ($) {
    $(window).load(function () {

        // Hidden 'currency_quote'
        document.querySelectorAll('input[type="text"]').forEach(function (input) {
            if ((input.name && input.name.includes('currency_quote')) || (input.id && input.id.includes('currency_quote'))) {
                input.style.display = 'none';
            }
        });

        // Selecionar os elementos
        let lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar = 1
        const mainForm = document.querySelector('#mainform')
        const fistH1 = mainForm.querySelector('h1')
        const submitP = mainForm.querySelector('p.submit')
        const tables = mainForm.querySelectorAll('table')

        if (mainform && fistH1 && submitP && tables) {
            // Criar uma nova div
            const newDiv = document.createElement('div')
            newDiv.id = 'lknIntegrationRedeForWoocommerceSettingsLayoutDiv'

            // Acessar o próximo elemento após fistH1
            let currentElement = fistH1 // Começar com fistH1

            // Mover fistH1 e todos os elementos entre fistH1 e submitP para a nova div
            while (currentElement && currentElement !== submitP.nextElementSibling) {
                const nextElement = currentElement.nextElementSibling // Armazenar o próximo elemento antes de mover    
                newDiv.appendChild(currentElement) // Mover o elemento atual para a nova div
                currentElement = nextElement // Atualizar currentElement para o próximo
            }

            // Mover submitP para a nova div
            newDiv.appendChild(submitP)

            // Adicionar a nova div ao mainForm
            mainForm.appendChild(newDiv)

            const subTitles = mainForm.querySelectorAll('.wc-settings-sub-title')
            const descriptionElement = mainForm.querySelector('p')
            const divElement = document.createElement('div')
            if (subTitles && descriptionElement) {
                // Criar a div que irá conter os novos elementos <p>
                divElement.id = 'lknIntegrationRedeForWoocommerceSettingsLayoutMenu'
                let aElements = []
                subTitles.forEach((subTitle, index) => {
                    // Criar um novo elemento <a> e adicionar o elemento <p> a ele
                    const aElement = document.createElement('a')
                    aElement.textContent = subTitle.textContent
                    aElement.href = '#' + subTitle.textContent
                    aElement.className = 'nav-tab'
                    aElement.onclick = (event) => {
                        // Verificar se é a aba Transactions/Transações
                        const tabText = subTitle.textContent.toLowerCase();
                        if (tabText === 'transactions' || tabText === 'transações') {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            // Usar URL do wp_localize_script
                            const analyticsUrl = lknWcRedeTranslationsInput.analytics_url;

                            // Abrir em nova aba
                            window.open(analyticsUrl, '_blank');
                            return false;
                        }
                        
                        lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar = index + 1
                        aElements.forEach((pElement, indexP) => {
                            if (indexP == index) {
                                aElements[index].className = 'nav-tab nav-tab-active'
                            } else {
                                aElements[indexP].className = 'nav-tab'
                            }
                        })
                        changeLayout()
                    }

                    // Adicionar o novo elemento <a> à div
                    divElement.appendChild(aElement)
                    aElements.push(aElement)

                    // Remover o subtítulo original
                    subTitle.parentNode.removeChild(subTitle)
                })

                aElements[0].className = 'nav-tab nav-tab-active'

                // Inserir a div após mainForm.querySelector('p')
                descriptionElement.parentNode.insertBefore(divElement, descriptionElement.nextSibling)

                tables.forEach((table, index) => {
                    if (index != 0 && index != 1) {
                        table.style.display = 'none'
                    }
                    table.menuIndex = index
                })

                function changeLayout() {
                    tables.forEach((table, index) => {
                        const currentSection = lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar;

                        if (currentSection === 1) {
                            // Primeira seção (General) mostra tabelas 0 e 1
                            if (index === 0 || index === 1) {
                                table.style.display = 'flex';
                            } else {
                                table.style.display = 'none';
                            }
                        } else {
                            // Outras seções mostram apenas sua tabela correspondente
                            // Seção 2 → tabela 2, Seção 3 → tabela 3, etc.
                            if (index === currentSection) {  // ← CORRIGIDO: remover o "- 1"
                                table.style.display = 'flex';
                            } else {
                                table.style.display = 'none';
                            }
                        }
                    })
                }

                // Corrige bug de layout quando alguma mensagem é exibida
                const divToMove = document.getElementById('lknIntegrationRedeForWoocommerceSettingsLayoutMenu')

                if (divToMove) {
                    const lknIntegrationRedeForWoocommerceSettingsLayoutDiv = document.getElementById('lknIntegrationRedeForWoocommerceSettingsLayoutDiv')

                    if (lknIntegrationRedeForWoocommerceSettingsLayoutDiv) {
                        const fifthElement = lknIntegrationRedeForWoocommerceSettingsLayoutDiv.children[3]

                        if (fifthElement) {
                            lknIntegrationRedeForWoocommerceSettingsLayoutDiv.insertBefore(divToMove, fifthElement.nextSibling)
                        }
                    }
                }

                // Caso o formulário tenha um campo inválido, força o click no menu em que o campo inválido está
                mainForm.addEventListener('invalid', function (event) {
                    const invalidField = event.target
                    if (invalidField) {
                        let parentNode = invalidField.parentNode
                        while (parentNode && parentNode.tagName !== 'TABLE') {
                            parentNode = parentNode.parentNode
                        }
                        if (parentNode) {
                            // Força o click no menu em que o campo inválido está
                            // TODO Fix this latter pElements don't exist
                            // if (pElements) {
                            //    pElements[parentNode.menuIndex - 1].parentNode.click()
                            // }
                        }
                    }
                }, true)

                // Verifica se há hash na URL e clica na tab correspondente
                const urlHash = window.location.hash
                if (urlHash) {
                    const targetElement = aElements.find(a => a.href.endsWith(urlHash))
                    if (targetElement) {
                        targetElement.click()
                    }
                }
            }

            const hrElement = document.createElement('hr')
            hrElement.style.margin = "2px 0px 40px"
            divElement.parentElement.insertBefore(hrElement, divElement.nextSibling)
            let descriptionP = hrElement.nextElementSibling;
            let menu = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsLayoutMenu');
            if (descriptionP && menu) {
                menu.parentElement.insertBefore(descriptionP, menu);
            }
        }

        document.querySelectorAll('.form-table > tbody > tr').forEach(tr => {
            const td = tr.querySelector('td');
            const th = tr.querySelector('th');
            if (td && th) {
                const span = th.querySelector("span")
                if (span) {
                    if (span.classList.contains("woocommerce-help-tip")) {
                        const ariaLabel = span.getAttribute('aria-label');
                        let desc = document.createElement('p');
                        desc.innerHTML = ariaLabel;
                        th.appendChild(desc);
                        span.style.display = 'none';
                    } else {
                        const novaSpan = th.querySelector(".lknIntegrationRedeForWoocommerceTooltiptext");
                        if (novaSpan) {
                            let desc = document.createElement('p');
                            desc.innerHTML = novaSpan.innerHTML.trim();
                            let lastChild = th.lastElementChild;
                            th.querySelector('label').appendChild(desc);
                            novaSpan.previousElementSibling.style.display = 'none';
                        }
                    }
                }
                let headerCart = document.createElement('div');
                let titleHeader = document.createElement('div');
                let descriptionTitle = document.createElement('div');
                let divHR = document.createElement('div');

                titleHeader.className = 'lkn-field-title';
                descriptionTitle.className = 'lkn-field-description';

                const titleTh = th.querySelector('label');
                const fieldId = titleTh.getAttribute('for');

                let textContent = titleTh.childNodes[0].textContent.trim();
                // Se o input tiver data-title-label, usa ele
                if (fieldId) {
                    const fieldConfig = document.getElementById(fieldId);
                    if (fieldConfig && fieldConfig.hasAttribute('data-title-label')) {
                        const customLabel = fieldConfig.getAttribute('data-title-label');
                        if (customLabel && customLabel.trim() !== '') {
                            textContent = customLabel.trim();
                        }
                    }
                }
                titleHeader.innerText = textContent;

                if (fieldId) {
                    const fieldConfig = document.getElementById(fieldId);
                    if (fieldConfig) {
                        const dataTitleDescription = fieldConfig.getAttribute('data-title-description');
                        descriptionTitle.innerHTML = dataTitleDescription ?? '';
                        
                        // Verificar se o campo tem atributo lkn-is-pro="true"
                        const isProField = fieldConfig.getAttribute('lkn-is-pro') === 'true';
                        if (isProField) {
                            // Criar o link PRO dinamicamente
                            const proLink = document.createElement('a');
                            proLink.className = 'lknIntegrationRedeForWoocommerceBecomePRO';
                            proLink.href = 'https://www.linknacional.com.br/wordpress/woocommerce/rede/';
                            proLink.target = '_blank';
                            
                            // Verificar se existe a variável global com o texto do PRO
                            if (typeof lknPhpProFieldsVariables !== 'undefined' && lknPhpProFieldsVariables.becomePRO) {
                                proLink.textContent = lknPhpProFieldsVariables.becomePRO;
                            } else {
                                proLink.textContent = 'PRO'; // fallback text
                            }
                            
                            titleHeader.appendChild(proLink);
                            
                            // Desabilitar o campo automaticamente
                            if (!fieldConfig.hasAttribute('disabled')) {
                                fieldConfig.disabled = true;
                            }
                            // fieldConfig.readOnly = true;
                            
                            // Se for um campo select, aplicar estilo cinza no select2
                            if (fieldConfig.tagName.toLowerCase() === 'select') {
                                const selectId = fieldConfig.id;
                                const select2Container = document.querySelector(`#select2-${selectId}-container`);
                                if (select2Container) {
                                    select2Container.style.opacity = '0.6';
                                    select2Container.style.filter = 'grayscale(0.5)';
                                    select2Container.style.pointerEvents = 'none';
                                }
                            }
                        }
                    } else {
                        descriptionTitle.innerHTML = '';
                    }
                }

                divHR.style.borderTop = '1px solid rgb(204, 204, 204)';
                divHR.style.margin = '8px 0px';
                divHR.style.width = '100%';

                headerCart.appendChild(titleHeader);
                headerCart.appendChild(descriptionTitle);
                headerCart.appendChild(divHR);

                const fieldset = td.firstElementChild;
                fieldset.insertBefore(headerCart, fieldset.firstElementChild);

                const divBody = document.createElement('div');
                divBody.className = 'lkn-rede-field-body';
                while (fieldset.childNodes.length > 2) {
                    divBody.appendChild(fieldset.childNodes[2]);
                }
                fieldset.appendChild(divBody);
                if (fieldId) {
                    const fieldConfig = document.getElementById(fieldId);
                    if (fieldConfig) {
                        const elementoPai = fieldConfig.getAttribute('merge-top') ? fieldConfig.getAttribute('merge-top') : false;
                        let input = document.getElementById(elementoPai) ?? false;
                        if (elementoPai && input) {
                            const label = input.parentElement;
                            const divBody = label.parentElement;
                            const fieldsetPai = divBody.parentElement;
                            const fieldsetFilho = td.querySelector('fieldset');

                            let containerCampos = fieldsetPai.querySelector('.lkn-rede-container-campos');

                            if (!containerCampos) {
                                containerCampos = document.createElement('div');
                                fieldsetPai.appendChild(containerCampos);
                                containerCampos.classList.add('lkn-rede-container-campos');
                            }

                            containerCampos.append(fieldsetFilho);
                            tr.style.display = 'none';
                        }
                        const numberLabel = fieldConfig.getAttribute('type-number-label') ? fieldConfig.getAttribute('type-number-label') : false;
                        if (numberLabel) {
                            fieldConfig.style.marginRight = '10px'
                            fieldConfig.outerHTML = `<div style="display: flex;">${fieldConfig.outerHTML}<label style="color: #2C3338;">${numberLabel}</label></div>`;
                        }
                        const mergeCheckbox = fieldConfig.getAttribute('merge-checkbox') ? fieldConfig.getAttribute('merge-checkbox') : false;
                        if (mergeCheckbox) {
                            const parentInput = document.getElementById(mergeCheckbox).closest('div.lkn-rede-field-body');
                            if (parentInput) {
                                const labelCheckbox = fieldConfig.closest('label');
                                fieldConfig.closest('tr').style.display = 'none';
                                parentInput.appendChild(labelCheckbox);
                            }
                        }

                        // Adicionar preview de imagem para o campo de template style
                        if (fieldId === 'woocommerce_rede_debit_3ds_template_style' && typeof lknWcRedeLayoutSettings !== 'undefined') {
                            const previewContainer = document.createElement('div');
                            previewContainer.style.marginTop = '10px';
                            
                            const previewLabel = document.createElement('p');
                            previewLabel.textContent = 'Preview:';
                            previewLabel.style.margin = '5px 0';
                            previewLabel.style.fontWeight = 'bold';
                            
                            const previewImage = document.createElement('img');
                            previewImage.style.maxWidth = '200px';
                            previewImage.style.width = '100%';
                            previewImage.style.border = '1px solid #ddd';
                            previewImage.style.borderRadius = '4px';
                            
                            // Função para atualizar a imagem
                            function updatePreviewImage() {
                                const selectedValue = fieldConfig.value;
                                if (selectedValue === 'basic' && lknWcRedeLayoutSettings.basic) {
                                    previewImage.src = lknWcRedeLayoutSettings.basic;
                                    previewImage.alt = 'Basic Template Preview';
                                } else if (selectedValue === 'modern' && lknWcRedeLayoutSettings.modern) {
                                    previewImage.src = lknWcRedeLayoutSettings.modern;
                                    previewImage.alt = 'Modern Template Preview';
                                }
                            }
                            
                            // Configurar imagem inicial
                            updatePreviewImage();
                            
                            // Adicionar evento de mudança usando Select2 event
                            $(fieldConfig).on('select2:select', function() {
                                updatePreviewImage();
                            });
                            
                            // Fallback para mudanças diretas no select (caso Select2 não esteja ativo)
                            fieldConfig.addEventListener('change', function() {
                                updatePreviewImage();
                            });
                            
                            // Montar a estrutura
                            previewContainer.appendChild(previewLabel);
                            previewContainer.appendChild(previewImage);
                            divBody.appendChild(previewContainer);
                        }
                    }
                }
            }
        })

        const divGeral = document.createElement('div');
        const card = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsCardContainer');
        const divSettingsLayout = document.querySelector('#lknIntegrationRedeForWoocommerceSettingsLayoutDiv');
        divSettingsLayout.parentElement.appendChild(divGeral);
        divGeral.appendChild(divSettingsLayout);
        divGeral.appendChild(card);
        divGeral.className = 'lknIntegrationRedeForWoocommerceDivGeral';

        // === LÓGICA DO WHATSAPP - INÍCIO ===
        const sendConfigsInput = document.querySelector('input[id^="woocommerce_"][id$="_send_configs"]');

        // Lógica para customizar o botão de suporte WhatsApp
        if (sendConfigsInput) {
            // Extrai o nome do gateway do id
            const idMatch = sendConfigsInput.id.match(/^woocommerce_(.+)_send_configs$/);
            let gatewayName = '';
            if (idMatch && idMatch[1]) {
                gatewayName = idMatch[1].replace(/_/g, ' ');
                gatewayName = gatewayName.charAt(0).toUpperCase() + gatewayName.slice(1);
            }

            // Define o label do botão
            const supportLabel = lknWcRedeTranslations && lknWcRedeTranslations.sendConfigs ? lknWcRedeTranslations.sendConfigs : 'Suporte';
            sendConfigsInput.value = `${supportLabel}`.trim();

            // Adiciona o ícone do WhatsApp antes do texto
            sendConfigsInput.style.width = 'fit-content';
            sendConfigsInput.style.paddingTop = '10px';
            sendConfigsInput.style.paddingBottom = '10px';
            sendConfigsInput.style.paddingLeft = '32px';
            sendConfigsInput.style.paddingRight = '18px';
            sendConfigsInput.style.background = 'url("https://cdn.simpleicons.org/whatsapp/white") no-repeat 8px center/18px, #25d366';
            sendConfigsInput.style.color = '#fff';
            sendConfigsInput.style.fill = '#fff';
            sendConfigsInput.style.border = 'none';
            sendConfigsInput.style.borderRadius = '2px';
            sendConfigsInput.style.fontWeight = 'bold';
            sendConfigsInput.style.cursor = 'pointer';
            sendConfigsInput.style.outline = '#25d366';
            sendConfigsInput.style.transition = 'background 0.2s';
            sendConfigsInput.onmouseover = function() {
                this.style.backgroundColor = '#128c7e';
            };
            sendConfigsInput.onmouseout = function() {
                this.style.backgroundColor = '#25d366';
            };
            sendConfigsInput.style.backgroundColor = '#25d366';

            // Altera o tipo para button (opcional, se não for submit)
            sendConfigsInput.type = 'button';

            // Adiciona ação para abrir WhatsApp com mensagem formatada
            const whatsappNumber = lknWcRedeTranslationsInput && lknWcRedeTranslationsInput.whatsapp_number ? lknWcRedeTranslationsInput.whatsapp_number : '55999999999';
            const gatewayId = lknWcRedeTranslationsInput && lknWcRedeTranslationsInput.gateway_id ? lknWcRedeTranslationsInput.gateway_id : 'unknown_gateway';
            const siteDomain = lknWcRedeTranslationsInput && lknWcRedeTranslationsInput.site_domain ? lknWcRedeTranslationsInput.site_domain : window.location.hostname;
            sendConfigsInput.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Remove classes de animação imediatamente após o clique
                this.classList.remove('is-busy', 'components-button__busy-animation', 'animation');
                const settings = lknWcRedeTranslationsInput.gateway_settings || {};
                let message = '#suporte-info Olá! Preciso de suporte com meu gateway de pagamento Rede. Estou com problemas na transação e segue os dados para verificação:';
                message += ` Gateway: ${gatewayId} | Site: ${siteDomain} | Plugin: lkn-integration-rede-for-woocommerce v${lknWcRedeTranslationsInput.version_free} | Plugin dependente: ${lknWcRedeTranslationsInput.version_pro && lknWcRedeTranslationsInput.version_pro !== 'N/A' ? 'lkn-integration-rede-for-woocommerce-pro v' + lknWcRedeTranslationsInput.version_pro : 'N/A'} | `;
                message += gatewayId.includes('pix') ? 'endpoint: ' + (lknWcRedeTranslationsInput.endpointStatus ? 'true' : 'null') + ' | ' : '';
                const sensitiveKeys = ['pv', 'token', 'license', 'card_token'];

                Object.keys(settings).forEach(function(key) {
                    if (key === 'rede') return;
                    if (key === 'developers') return;
                    if (key === 'gateway') return;
                    if (key === 'credit_options') return;
                    if (key === 'currency_quote') return;
                    if (key === 'endpoint') return;
                    if (key === 'send_configs') return;
                    if (key === 'general') return; // Ignora 'general'
                    if (key === 'validate_license') return; // Ignora 'validate_license'
                    if (key === 'pro') return; // Ignora 'pro'
                    if (key === 'fake_license_field') return; // Ignora 'fake_license_field'
                    if (key === 'fake_cardholder_field') return; // Ignora 'fake_cardholder_field'
                    if (key === 'fake_layout') return; // Ignora 'fake_layout'
                    if (key === 'fake_and_more_field') return; // Ignora 'fake_and_more_field',
                    if (key === 'transactions') return; // Ignora 'transactions'

                    let value = settings[key];

                    // 1. Normalização de valores vazios/nulos
                    if (value === undefined || value === null || value === '') {
                        value = 'null';
                    }

                    // 2. Lógica de Censura Dinâmica
                    if (sensitiveKeys.includes(key) && value !== 'null') {
                        const strValue = String(value);
                        const len = strValue.length;

                        // Regra: Mostra no máximo 4, mas nunca mais que 1/3 da string para garantir segurança em strings curtas
                        // Ex: Se tem 32 chars, mostra 4. Se tem 4 chars, mostra 1. Se tem 2, mostra 0.
                        const keep = Math.min(4, Math.floor(len / 3)); 
                        
                        const start = strValue.slice(0, keep);
                        const end = strValue.slice(-keep);
                        // Se keep for 0, o slice(-0) pega tudo, então tratamos isso:
                        const safeEnd = keep > 0 ? strValue.slice(-keep) : '';
                        
                        // O meio é preenchido com asteriscos fixos (***) ou baseados no tamanho real
                        const middle = '*'.repeat(Math.max(1, len - (keep * 2)));

                        value = `${start}${middle}${safeEnd}`;
                    }

                    message += ` ${key}: ${value} |`;
                });
                message += ' Aguardo retorno, obrigado!';
                window.open(`https://api.whatsapp.com/send/?phone=${whatsappNumber}&text=${encodeURIComponent(message)}`,'_blank');
            };
        }
        // === LÓGICA DO WHATSAPP - FIM ===

    })
})(jQuery)
