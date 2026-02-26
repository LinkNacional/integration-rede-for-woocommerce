(function ($) {
  $(window).load(function () {
    // Detectar a página atual
    const adminPage = lknFindGetParameter('section')
    const pluginPages = [
      'maxipago_credit',
      'maxipago_debit',
      'maxipago_pix',
      'rede_credit',
      'rede_debit',
      'rede_pix',
      'integration_rede_pix'
    ]

    // Function to create feature message components
    function createFeatureMessage(iconHtml, title, text, href) {
      // Se href for fornecido, cria um <a>, senão um <div>
      const featureMessage = document.createElement(href ? 'a' : 'div');
      featureMessage.className = 'custom-feature-message';
      if (href) {
        featureMessage.href = href;
        featureMessage.target = '_blank';
        featureMessage.rel = 'noopener noreferrer';
        featureMessage.style.textDecoration = 'none';
        featureMessage.style.color = 'inherit';
      }

      // Ícone (HTML)
      const infoIcon = document.createElement('span');
      infoIcon.className = 'feature-icon';
      infoIcon.innerHTML = iconHtml;

      // Container para título + texto
      const contentDiv = document.createElement('div');
      contentDiv.className = 'feature-message-content';
      contentDiv.innerHTML = `<strong>${title}</strong><br>${text}`;

      featureMessage.appendChild(infoIcon);
      featureMessage.appendChild(contentDiv);

      return featureMessage;
    }

    function lknFindGetParameter(parameterName) {
      let result = null
      let tmp = []
      location.search
        .substr(1)
        .split('&')
        .forEach(function (item) {
          tmp = item.split('=')
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1])
        })
      return result
    }

    // Insere as mensagens no container de configurações
    const cardContainer = document.getElementById('lknIntegrationRedeForWoocommerceSettingsCardContainer');
    
    if (cardContainer) {
      // Cria o primeiro bloco de mensagem com ícone do WhatsApp, título e texto
      const whatsappIcon = '<svg style="width: 18px; height: 18px; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24" fill="#388E3C" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.087z"/></svg>';
      // Monta o link do WhatsApp com mensagem padrão
      let whatsappHref = '';
      if (lknPhpVariables.whatsapp_number) {
        const wppNumber = encodeURIComponent(lknPhpVariables.whatsapp_number.replace(/\D/g, ''));
        let domain = lknPhpVariables.site_url;
        const wppMsg = encodeURIComponent(`#suporte-wp Preciso de ajuda rápida com meu Site: ${domain}!`);
        whatsappHref = `https://wa.me/${wppNumber}?text=${wppMsg}`;
      }
      const featureMessage1 = createFeatureMessage(
        whatsappIcon,
        'Suporte WordPress via WhatsApp',
        'Atendimento rápido na hora que você mais precisa.',
        whatsappHref || undefined
      );

      // Cria o segundo bloco de mensagem
      const featureMessage2 = createFeatureMessage(
        '✔️',
        'Google Pay e Tabela de Transações:',
        'Disponíveis exclusivamente para assinantes Pro e clientes de nossa hospedagem.'
      );

      // Adiciona as mensagens ao container
      cardContainer.appendChild(featureMessage1);
      cardContainer.appendChild(featureMessage2);
      
      // Criar cartão promocional
      const promotionalCard = document.createElement('div');
      promotionalCard.className = 'woo-better-promotional-card';

      // Adiciona um elemento de fundo decorativo
      const backgroundDecor = document.createElement('div');
      backgroundDecor.className = 'promotional-card-background-decor';
      promotionalCard.appendChild(backgroundDecor);

      // Conteúdo do cartão
      const cardContent = document.createElement('div');
      cardContent.className = 'promotional-card-content';

      // Título do plugin
      const cardTitle = document.createElement('h3');
      cardTitle.className = 'promotional-card-title';
      cardTitle.textContent = 'Plugin Link de Pagamento de Faturas';

      // Traço decorativo
      const titleDivider = document.createElement('hr');
      titleDivider.className = 'promotional-card-title-divider';
      titleDivider.style.cssText = 'width: 100%; height: 1px; background: white; border: none; margin: 8px 0 16px 0;';

      // Descrição do plugin
      const cardDescription = document.createElement('p');
      cardDescription.className = 'promotional-card-description';
      cardDescription.textContent = 'O Plugin Link de Pagamento oferece a solução completa para o seu negócio. Gere links personalizados, aceite pagamento em múltiplos cartões, configure cobranças recorrentes, crie orçamentos e venda diretamente pelo WhatsApp!';

      // Container dos botões
      const buttonsContainer = document.createElement('div');
      buttonsContainer.className = 'promotional-card-buttons';

      // Botão Saiba mais (sempre presente)
      const learnMoreButton = document.createElement('button');
      learnMoreButton.className = 'promotional-card-button learn-more';
      learnMoreButton.textContent = 'Saiba mais';
      
      learnMoreButton.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.open('https://br.wordpress.org/plugins/invoice-payment-for-woocommerce/', '_blank');
      });

      buttonsContainer.appendChild(learnMoreButton);

      // Botão Instalar (apenas se o plugin não estiver instalado)
      if (!lknPhpVariables.invoice_plugin_installed) {
        const installButton = document.createElement('button');
        installButton.className = 'promotional-card-button install';
        installButton.textContent = 'Instalar';
        
        installButton.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          const installUrl = `/wp-admin/update.php?action=install-plugin&plugin=${lknPhpVariables.plugin_slug}&_wpnonce=${lknPhpVariables.install_nonce}`;
          window.open(installUrl, '_blank');
        });

        buttonsContainer.appendChild(installButton);
      }

      // Monta o conteúdo do cartão
      cardContent.appendChild(cardTitle);
      cardContent.appendChild(titleDivider);
      cardContent.appendChild(cardDescription);
      cardContent.appendChild(buttonsContainer);
      promotionalCard.appendChild(cardContent);
      
      // Adiciona o cartão promocional ao container
      cardContainer.appendChild(promotionalCard);
    }

    // Inserir campos PRO se estiver em uma página de plugin e não for versão PRO ativa
    if (adminPage && pluginPages.includes(adminPage)) {
      const wcForm = document.getElementById('mainform')
      
      if (!lknPhpVariables.isProActive) {
        const submitButton = wcForm.querySelector('button[type="submit"]').parentElement
        if (submitButton && typeof lknIntegrationRedeForWoocommerceProFields === 'function') {
          submitButton.insertAdjacentHTML('beforebegin', lknIntegrationRedeForWoocommerceProFields(adminPage))
        }
      }
    }
  })
})(jQuery)
