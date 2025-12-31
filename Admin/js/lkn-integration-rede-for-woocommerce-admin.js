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
    function createFeatureMessage(iconText, messageLines) {
      const featureMessage = document.createElement('div');
      featureMessage.className = 'custom-feature-message';

      // Adiciona o ícone de informação
      const infoIcon = document.createElement('span');
      infoIcon.className = 'feature-icon';
      infoIcon.textContent = iconText;

      // Adiciona o texto da mensagem
      const textContainer = document.createElement('div');
      textContainer.className = 'feature-text-container';

      // Adiciona as linhas de texto
      messageLines.forEach(line => {
        const messageLine = document.createElement('span');
        messageLine.className = 'feature-text-line';
        messageLine.innerHTML = line;
        textContainer.appendChild(messageLine);
      });

      // Adiciona o ícone e o texto ao componente de mensagem
      featureMessage.appendChild(infoIcon);
      featureMessage.appendChild(textContainer);

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
      // Cria o primeiro bloco de mensagem
      const featureMessage1 = createFeatureMessage('✔️', [
        '<strong>ATUALIZADO:</strong> Gateway Rede Débito agora suporta Cartões de Crédito e Débito com 3D Secure!'
      ]);

      // Cria o segundo bloco de mensagem
      const featureMessage2 = createFeatureMessage('✔️', [
        '<strong>NOVO:</strong> Sistema de Segurança Avançado: Implementação 3DS + OAuth2 da Rede para máxima proteção!'
      ]);

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
      cardDescription.textContent = 'O Plugin Link de Pagamento oferece a solução completa para o seu negócio. Gere links personalizados, aceite parcelamento em múltiplos cartões, configure cobranças recorrentes, crie orçamentos e venda diretamente pelo WhatsApp!';

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
