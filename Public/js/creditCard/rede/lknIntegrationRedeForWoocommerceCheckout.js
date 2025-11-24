import React from 'react';
import Cards from 'react-credit-cards';
import 'react-credit-cards/es/styles-compiled.css';
const settingsRedeCredit = window.wc.wcSettings.getSetting('rede_credit_data', {});
const labelRedeCredit = window.wp.htmlEntities.decodeEntities(settingsRedeCredit.title);
// Obtendo o nonce da variável global
const nonceRedeCredit = settingsRedeCredit.nonceRedeCredit;
const translationsRedeCredit = settingsRedeCredit.translations;
const minInstallmentsRede = settingsRedeCredit.minInstallmentsRede.replace(',', '.');
const ContentRedeCredit = props => {
  const totalAmountFloat = settingsRedeCredit.cartTotal;
  const [selectedValue, setSelectedValue] = window.wp.element.useState('1');
  const handleSortChange = event => {
    const value = String(event.target.value); // Garante que seja string
    setSelectedValue(value);
    updateCreditObject('rede_credit_installments', value);
  };
  const {
    eventRegistration,
    emitResponse
  } = props;
  const {
    onPaymentSetup
  } = eventRegistration;
  const wcComponents = window.wc.blocksComponents;
  const [creditObject, setCreditObject] = window.wp.element.useState({
    rede_credit_number: '',
    rede_credit_installments: '1',
    rede_credit_expiry: '',
    rede_credit_cvc: '',
    rede_credit_holder_name: ''
  });
  const [focus, setFocus] = window.wp.element.useState('');
  const [options, setOptions] = window.wp.element.useState([]);

  // Função para buscar dados atualizados do backend e gerar as opções de installments (com debounce)
  let installmentTimeout = null;
  const generateRedeInstallmentOptions = async () => {
    if (installmentTimeout) clearTimeout(installmentTimeout);
    installmentTimeout = setTimeout(() => {
      try {
        window.jQuery.ajax({
          url: window.redeCreditAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'lkn_get_rede_credit_data',
            nonce: window.redeCreditAjax?.nonce || nonceRedeCredit
          },
          success: function (response) {
            if (response && Array.isArray(response.installments)) {
              // Remove tags HTML do label para exibir texto plano
              const plainOptions = response.installments.map(opt => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = opt.label;
                return {
                  ...opt,
                  label: tempDiv.textContent || tempDiv.innerText || ''
                };
              });
              
              // Remove todas as opções atuais e adiciona as novas
              setOptions(plainOptions);
              
              // Sempre garante que há uma opção selecionada válida
              const currentSelection = selectedValue || '1';
              const validOption = plainOptions.find(opt => String(opt.key) === String(currentSelection));
              
              if (!validOption && plainOptions.length > 0) {
                // Se a seleção atual não é válida, seleciona a primeira opção
                const firstOption = String(plainOptions[0].key);
                setSelectedValue(firstOption);
                updateCreditObject('rede_credit_installments', firstOption);
              } else if (validOption && selectedValue !== String(validOption.key)) {
                // Se a opção é válida mas o state não está sincronizado, atualiza
                setSelectedValue(String(validOption.key));
                updateCreditObject('rede_credit_installments', String(validOption.key));
              }
            }
          },
          error: function () {
            // Se falhar, mantém as opções atuais
          }
        });
      } catch (error) {
        // Se falhar, mantém as opções atuais
      }
    }, 400); // 400ms de debounce
  };

  // Intercepta requisições para atualizar parcelas após mudanças no shipping
  window.wp.element.useEffect(() => {
    // Chama só uma vez ao carregar a página
    generateRedeInstallmentOptions();

    // Intercepta o fetch original para capturar requisições de shipping
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
      const [url, options] = args;
      
      // Verifica se é uma requisição para select-shipping-rate
      if (url && url.includes('/wp-json/wc/store/v1/cart/select-shipping-rate')) {
        // Executa a requisição original
        return originalFetch.apply(this, args).then(response => {
          // Clona a response para poder ler o conteúdo
          const responseClone = response.clone();
          
          // Verifica se a requisição foi bem-sucedida
          if (response.ok) {
            // Aguarda um breve momento para a atualização do carrinho e então atualiza as parcelas
            setTimeout(() => {
              // Limpa as opções atuais e busca as novas
              setOptions([]);
              setSelectedValue('1');
              updateCreditObject('rede_credit_installments', '1'); // Garante que seja string
              generateRedeInstallmentOptions();
            }, 500);
          }
          
          // Retorna a response original
          return response;
        }).catch(error => {
          // Em caso de erro, retorna a response original
          return originalFetch.apply(this, args);
        });
      }
      
      // Para outras requisições, executa normalmente
      return originalFetch.apply(this, args);
    };

    // Cleanup: restaura o fetch original quando o componente é desmontado
    return () => {
      window.fetch = originalFetch;
    };
  }, []);

  // Observa eventos de atualização do WooCommerce Blocks (cart/checkout)
  // ...removido: useEffect de eventos, agora só observa cartTotal...
  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.rede_credit_number;
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '');
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim();
    return formattedValue;
  };
  const updateCreditObject = (key, value) => {
    let isValidDate = false;
    switch (key) {
      case 'rede_credit_expiry':
        if (value.length > 7) return;

        // Verifica se o valor é uma data válida (MM/YY)
        isValidDate = /^\d{2}\/\d{2}$/.test(value);
        if (!isValidDate) {
          // Remove caracteres não numéricos
          const cleanedValue = value?.replace(/\D/g, '');
          let formattedValue = cleanedValue?.replace(/^(.{2})/, '$1 / ')?.trim();

          // Se o tamanho da string for 5, remove o espaço e a barra adicionados anteriormente
          if (formattedValue.length === 4) {
            formattedValue = formattedValue.replace(/\s\//, '');
          }

          // Atualiza o estado
          setCreditObject({
            ...creditObject,
            [key]: formattedValue
          });
        }
        return;
      case 'rede_credit_cvc':
        if (!/^\d+$/.test(value) && value !== '' || value.length > 4) return;
        break;
      default:
        break;
    }
    setCreditObject({
      ...creditObject,
      [key]: value
    });
  };
  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos do creditObject estão preenchidos
      const allFieldsFilled = Object.values(creditObject).every(field => {
        // Garante que o campo seja uma string antes de chamar trim()
        const fieldStr = typeof field === 'string' ? field : String(field);
        return fieldStr.trim() !== '';
      });
      if (allFieldsFilled) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              rede_credit_number: creditObject.rede_credit_number,
              rede_credit_installments: creditObject.rede_credit_installments,
              rede_credit_expiry: creditObject.rede_credit_expiry,
              rede_credit_cvc: creditObject.rede_credit_cvc,
              rede_credit_holder_name: creditObject.rede_credit_holder_name,
              rede_card_nonce: nonceRedeCredit
            }
          }
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsRedeCredit.fieldsNotFilled
      };
    });

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe();
    };
  }, [creditObject,
    // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup, translationsRedeCredit // Adicione translationsRedeCredit como dependência
  ]);
  return (
    <React.Fragment>
      <Cards
        number={creditObject.rede_credit_number}
        name={creditObject.rede_credit_holder_name}
        expiry={creditObject.rede_credit_expiry.replace(/\s+/g, '')}
        cvc={creditObject.rede_credit_cvc}
        placeholders={{
          name: 'NOME',
          expiry: 'MM/ANO',
          cvc: 'CVC',
          number: '•••• •••• •••• ••••'
        }}
        locale={{ valid: 'VÁLIDO ATÉ' }}
        focused={focus}
      />
      <wcComponents.TextInput
        id="rede_credit_holder_name"
        label={translationsRedeCredit.nameOnCard}
        value={creditObject.rede_credit_holder_name}
        maxLength={30}
        onChange={value => updateCreditObject('rede_credit_holder_name', value)}
        onFocus={() => setFocus('name')}
      />
      <wcComponents.TextInput
        id="rede_credit_number"
        label={translationsRedeCredit.cardNumber}
        value={formatCreditCardNumber(creditObject.rede_credit_number)}
        onChange={value => updateCreditObject('rede_credit_number', formatCreditCardNumber(value))}
        onFocus={() => setFocus('number')}
      />
      <wcComponents.TextInput
        id="rede_credit_expiry"
        label={translationsRedeCredit.cardExpiringDate}
        value={creditObject.rede_credit_expiry}
        onChange={value => updateCreditObject('rede_credit_expiry', value)}
        onFocus={() => setFocus('expiry')}
      />
      <wcComponents.TextInput
        id="rede_credit_cvc"
        label={translationsRedeCredit.securityCode}
        value={creditObject.rede_credit_cvc}
        onChange={value => updateCreditObject('rede_credit_cvc', value)}
        onFocus={() => setFocus('cvc')}
      />
      {options.length > 0 && (
        <div className="lknIntegrationRedeForWoocommerceSelectBlocks">
          <label>{translationsRedeCredit.installments}</label>
          <select
            value={selectedValue}
            onChange={handleSortChange}
            readOnly={false}
          >
            {options.map(opt => (
              <option key={opt.key} value={opt.key}>{opt.label}</option>
            ))}
          </select>
        </div>
      )}
    </React.Fragment>
  );
}
const BlockGatewayRedeCredit = {
  name: 'rede_credit',
  label: labelRedeCredit,
  content: window.wp.element.createElement(ContentRedeCredit),
  edit: window.wp.element.createElement(ContentRedeCredit),
  canMakePayment: () => true,
  ariaLabel: labelRedeCredit,
  supports: {
    features: settingsRedeCredit.supports
  }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayRedeCredit);