(function ($) {
    $(window).load(function () {
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
                aElements = []
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
                        switch (lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar) {
                            case 1:
                                if (index == 0 || index == 1) {
                                    table.style.display = 'block'
                                } else {
                                    table.style.display = 'none'
                                }
                                break
                            case 2:
                                if (index == 2) {
                                    table.style.display = 'block'
                                } else {
                                    table.style.display = 'none'
                                }
                                break
                            case 3:
                                if (index == 3) {
                                    table.style.display = 'block'
                                } else {
                                    table.style.display = 'none'
                                }
                                break
                            case 4:
                                if (index == 4) {
                                    table.style.display = 'block'
                                } else {
                                    table.style.display = 'none'
                                }
                                break
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
            divElement.parentElement.insertBefore(hrElement, divElement.nextSibling)
        }
    })
})(jQuery)
