(function ($) {
    $(window).load(function () {
        // Selecionar os elementos
        var lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar = 1;
        const mainForm = document.querySelector('#mainform');
        const fistH1 = mainForm.querySelector('h1');
        const submitP = mainForm.querySelector('p.submit');
        const tables = mainForm.querySelectorAll('table');

        if(mainform && fistH1 && submitP && tables){
            // Criar uma nova div
            const newDiv = document.createElement('div');
            newDiv.id = 'lknIntegrationRedeForWoocommerceSettingsLayoutDiv';
    
            // Acessar o próximo elemento após fistH1
            let currentElement = fistH1; // Começar com fistH1
    
            // Mover fistH1 e todos os elementos entre fistH1 e submitP para a nova div
            while (currentElement && currentElement !== submitP.nextElementSibling) {
                const nextElement = currentElement.nextElementSibling; // Armazenar o próximo elemento antes de mover
                newDiv.appendChild(currentElement); // Mover o elemento atual para a nova div
                currentElement = nextElement; // Atualizar currentElement para o próximo
            }
    
            // Mover submitP para a nova div
            newDiv.appendChild(submitP);
    
            // Adicionar a nova div ao mainForm
            mainForm.appendChild(newDiv);
    
            let subTitles = mainForm.querySelectorAll('.wc-settings-sub-title');
            let descriptionElement = mainForm.querySelector('p');
            
            if(subTitles && descriptionElement){
                // Criar a div que irá conter os novos elementos <p>
                let divElement = document.createElement('div');
                divElement.id = 'lknIntegrationRedeForWoocommerceSettingsLayoutMenu';
        
                var pElements = [];
                subTitles.forEach((subTitle, index) => {
                    // Criar um novo elemento <p> com a descrição do subtítulo
                    let pElement = document.createElement('p');
        
                    //Marca o primeiro elemento quando carregar a página
                    if(index == 0){
                        pElement.className = 'active';
                    }
        
                    pElements.push(pElement);
                    pElement.textContent = subTitle.textContent;
        
                    // Criar um novo elemento <a> e adicionar o elemento <p> a ele
                    let aElement = document.createElement('a');
                    aElement.appendChild(pElement);
                    aElement.href = '#';
                    aElement.onclick = (event) => {
                        lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar = index + 1;
                        
                        pElements.forEach((pElement, indexP) => {
                            if(indexP == index){
                                pElements[index].className = 'active';
                            }else{
                                pElements[indexP].className = '';
                            }
                        });
                        changeLayout()
                    }
        
                    // Adicionar o novo elemento <a> à div
                    divElement.appendChild(aElement);
        
                    // Adicionar um "|" entre os elementos <a>, mas não no início do primeiro ou no final do último
                    if (index < subTitles.length - 1) {
                        let separator = document.createTextNode(' | ');
                        divElement.appendChild(separator);
                    }
        
                    // Remover o subtítulo original
                    subTitle.parentNode.removeChild(subTitle);
                });
        
                // Inserir a div após mainForm.querySelector('p')
                descriptionElement.parentNode.insertBefore(divElement, descriptionElement.nextSibling);
        
                tables.forEach((table, index) => {
                    if(index != 0 && index != 1) {
                        table.style.display = 'none';
                    }
                    table.menuIndex = index;
                });
        
        
                function changeLayout(){
                    tables.forEach((table, index) => {
                        switch(lknIntegrationRedeForWoocommerceSettingsLayoutMenuVar){
                            case 1:
                                if(index == 0 || index == 1) {
                                    table.style.display = 'block';
                                }else{
                                    table.style.display = 'none';
                                }
                            break;
                            case 2:
                                if(index == 2) {
                                    table.style.display = 'block';
                                }else{
                                    table.style.display = 'none';
                                }
                            break
                            case 3:
                                if(index == 3) {
                                    table.style.display = 'block';
                                }else{
                                    table.style.display = 'none';
                                }
                            break
                            case 4:
                                if(index == 4) {
                                    table.style.display = 'block';
                                }else{
                                    table.style.display = 'none';
                                }
                            break
                        }
                    });
                    
                }
        
                // Corrige bug de layout quando alguma mensagem é exibida
                var divToMove = document.getElementById('lknIntegrationRedeForWoocommerceSettingsLayoutMenu');

                if (divToMove) {
                    var lknIntegrationRedeForWoocommerceSettingsLayoutDiv = document.getElementById('lknIntegrationRedeForWoocommerceSettingsLayoutDiv');

                    if (lknIntegrationRedeForWoocommerceSettingsLayoutDiv) {
                        var fifthElement = lknIntegrationRedeForWoocommerceSettingsLayoutDiv.children[3];

                        if (fifthElement) {
                            lknIntegrationRedeForWoocommerceSettingsLayoutDiv.insertBefore(divToMove, fifthElement.nextSibling);
                        }
                    }
                }

                //Caso o formulário tenha um campo inválido, força o click no menu em que o campo inválido está
                mainForm.addEventListener('invalid', function (event) {
                    const invalidField = event.target;
                    if (invalidField) {
                        let parentNode = invalidField.parentNode;
                        while (parentNode && parentNode.tagName !== 'TABLE') {
                            parentNode = parentNode.parentNode;
                        }
                        if (parentNode) {
                            //Força o click no menu em que o campo inválido está
                            pElements[parentNode.menuIndex-1].parentNode.click()
                        }
                    }
                }, true);
            }
        }
    });
})(jQuery);
