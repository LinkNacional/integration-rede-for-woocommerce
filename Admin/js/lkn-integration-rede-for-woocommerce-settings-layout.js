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
                    aElement.href = '#'
                    aElement.className = 'nav-tab'
                    aElement.onclick = (event) => {
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
                            th.insertBefore(desc, lastChild);
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
                const textContent = titleTh.childNodes[0].textContent.trim();
                titleHeader.innerText = textContent;

                const fieldId = titleTh.getAttribute('for');
                if (fieldId) {
                    const fieldConfig = document.getElementById(fieldId);
                    if (fieldConfig) {
                        const dataTitleDescription = fieldConfig.getAttribute('data-title-description');
                        descriptionTitle.innerHTML = dataTitleDescription ?? '';
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
                        const elementoPai = fieldConfig.getAttribute('merge-top') ? "woocommerce_rede_credit_" + fieldConfig.getAttribute('merge-top') : false;
                        if (elementoPai) {
                            const input = document.getElementById(elementoPai);
                            const label = input.parentElement;
                            const divBody = label.parentElement;
                            const fieldsetPai = divBody.parentElement;
                            const fieldsetFilho = td.querySelector('fieldset');
                            fieldsetPai.append(fieldsetFilho);
                            tr.style.display = 'none';
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
    })
})(jQuery)
