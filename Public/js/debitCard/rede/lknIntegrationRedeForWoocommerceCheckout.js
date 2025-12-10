import React from 'react';
import Cards from 'react-credit-cards';
import 'react-credit-cards/es/styles-compiled.css';
const settingsRedeDebit = window.wc.wcSettings.getSetting('rede_debit_data', {});
const labelRedeDebit = window.wp.htmlEntities.decodeEntities(settingsRedeDebit.title);
// Obtendo o nonce da variável global
const nonceRedeDebit = settingsRedeDebit.nonceRedeDebit;
const translationsRedeDebit = settingsRedeDebit.translations;
const cardTypeRestriction = settingsRedeDebit.cardTypeRestriction || 'debit_only';
const minInstallmentsRede = settingsRedeDebit.minInstallmentsRede ? settingsRedeDebit.minInstallmentsRede.replace(',', '.') : '5.00';

const ContentRedeDebit = props => {
  const totalAmountFloat = settingsRedeDebit.cartTotal;
  const [selectedValue, setSelectedValue] = window.wp.element.useState('1');
  const handleSortChange = event => {
    const value = String(event.target.value); // Garante que seja string
    setSelectedValue(value);
    updateDebitObject('rede_debit_installments', value);
    
    // Faz requisição AJAX para atualizar a sessão de parcelas
    window.jQuery.ajax({
      url: window.redeDebitAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'lkn_update_installment_session',
        payment_method: 'rede_debit',
        installments: value,
        card_type: debitObject.card_type,
        nonce: window.redeDebitAjax?.installment_nonce
      },
      success: function (response) {
        // Invalida o cache do store para atualizar os dados apenas no sucesso da requisição
        if (window.wp && window.wp.data && window.wp.data.dispatch) {
          window.wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
        }
      },
      error: function () {
        // Em caso de erro, pode manter o comportamento atual ou mostrar uma mensagem
      }
    });
  };
  const {
    eventRegistration,
    emitResponse
  } = props;
  const {
    onPaymentSetup
  } = eventRegistration;
  const wcComponents = window.wc.blocksComponents;
  const [debitObject, setDebitObject] = window.wp.element.useState({
    rede_debit_number: '',
    rede_debit_installments: '1',
    rede_debit_expiry: '',
    rede_debit_cvc: '',
    rede_debit_holder_name: '',
    card_type: cardTypeRestriction === 'both' ? 'debit' : (cardTypeRestriction === 'credit_only' ? 'credit' : 'debit')
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
          url: window.redeDebitAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'lkn_get_rede_debit_data',
            card_type: debitObject.card_type,
            nonce: window.redeDebitAjax?.nonce || nonceRedeDebit
          },
          success: function (response) {
            // Invalida o cache do store para atualizar os dados
            if (window.wp && window.wp.data && window.wp.data.dispatch) {
              window.wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
            }
            
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
                updateDebitObject('rede_debit_installments', firstOption);
              } else if (validOption && selectedValue !== String(validOption.key)) {
                // Se a opção é válida mas o state não está sincronizado, atualiza
                setSelectedValue(String(validOption.key));
                updateDebitObject('rede_debit_installments', String(validOption.key));
              }
              
              // Invalida o cache do store após atualizar as opções
              if (window.wp && window.wp.data && window.wp.data.dispatch) {
                window.wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
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
    // Sempre faz a requisição para atualizar a sessão (tanto para crédito quanto débito)
    generateRedeInstallmentOptions();
    
    // Se for débito, ainda limpa as opções do frontend
    if (debitObject.card_type === 'debit') {
      setOptions([]);
      setSelectedValue('1');
      updateDebitObject('rede_debit_installments', '1');
    }

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
              // Só atualiza se for cartão de crédito
              if (cardTypeRestriction === 'credit_only' || debitObject.card_type === 'credit') {
                // Limpa as opções atuais e busca as novas
                setOptions([]);
                setSelectedValue('1');
                updateDebitObject('rede_debit_installments', '1'); // Garante que seja string
                generateRedeInstallmentOptions();
              }
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
  }, [debitObject.card_type]);

  const formatDebitCardNumber = value => {
    if (value?.length > 19) return debitObject.rede_debit_number;
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '');
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim();
    return formattedValue;
  };
  const updateDebitObject = (key, value) => {
    let isValidDate = false;
    switch (key) {
      case 'rede_debit_expiry':
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
          setDebitObject(prevState => ({
            ...prevState,
            [key]: formattedValue
          }));
        }
        return;
      case 'rede_debit_cvc':
        if (!/^\d+$/.test(value) && value !== '' || value.length > 4) return;
        break;
      default:
        break;
    }
    setDebitObject(prevState => ({
      ...prevState,
      [key]: value
    }));
  };
  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos obrigatórios estão preenchidos
      const requiredFields = ['rede_debit_number', 'rede_debit_expiry', 'rede_debit_cvc', 'rede_debit_holder_name'];
      if (cardTypeRestriction === 'both') {
        requiredFields.push('card_type');
      }
      
      const allFieldsFilled = requiredFields.every(field => debitObject[field] && debitObject[field].trim() !== '');
      
      if (allFieldsFilled) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              rede_debit_number: debitObject.rede_debit_number,
              rede_debit_installments: debitObject.rede_debit_installments,
              rede_debit_expiry: debitObject.rede_debit_expiry,
              rede_debit_cvc: debitObject.rede_debit_cvc,
              rede_debit_holder_name: debitObject.rede_debit_holder_name,
              rede_debit_card_type: debitObject.card_type,
              rede_card_nonce: nonceRedeDebit
            }
          }
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsRedeDebit.fieldsNotFilled
      };
    });

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe();
    };
  }, [debitObject,
  // Adiciona debitObject como dependência
  emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup, translationsRedeDebit // Adicione translationsRedeDebit como dependência
  ]);
  return (
    <React.Fragment>
      <Cards
        number={debitObject.rede_debit_number}
        name={debitObject.rede_debit_holder_name}
        expiry={debitObject.rede_debit_expiry.replace(/\s+/g, '')}
        cvc={debitObject.rede_debit_cvc}
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
        id="rede_debit_holder_name"
        label={translationsRedeDebit.nameOnCard}
        value={debitObject.rede_debit_holder_name}
        maxLength={30}
        onChange={value => updateDebitObject('rede_debit_holder_name', value)}
        onFocus={() => setFocus('name')}
      />
      <wcComponents.TextInput
        id="rede_debit_number"
        label={translationsRedeDebit.cardNumber}
        value={formatDebitCardNumber(debitObject.rede_debit_number)}
        onChange={value => updateDebitObject('rede_debit_number', formatDebitCardNumber(value))}
        onFocus={() => setFocus('number')}
      />
      <wcComponents.TextInput
        id="rede_debit_expiry"
        label={translationsRedeDebit.cardExpiringDate}
        value={debitObject.rede_debit_expiry}
        onChange={value => updateDebitObject('rede_debit_expiry', value)}
        onFocus={() => setFocus('expiry')}
      />
      <wcComponents.TextInput
        id="rede_debit_cvc"
        label={translationsRedeDebit.securityCode}
        value={debitObject.rede_debit_cvc}
        onChange={value => updateDebitObject('rede_debit_cvc', value)}
        onFocus={() => setFocus('cvc')}
      />
      {cardTypeRestriction === 'both' && (
        <div className="lknIntegrationRedeForWoocommerceSelectBlocks lknIntegrationRedeForWoocommerceSelect3dsInstallments">
          <label htmlFor="card_type_selector">{translationsRedeDebit.cardType}</label>
          <select
            id="card_type_selector"
            value={debitObject.card_type}
            onChange={e => {
              const value = e.target.value;
              updateDebitObject('card_type', value);
            }}
          >
            <option value="debit">{translationsRedeDebit.debitCard}</option>
            <option value="credit">{translationsRedeDebit.creditCard}</option>
          </select>
        </div>
      )}
      {(cardTypeRestriction === 'credit_only' || debitObject.card_type === 'credit') && options.length > 1 && (
        <div className="lknIntegrationRedeForWoocommerceSelectBlocks">
          <label>{translationsRedeDebit.installments}</label>
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
};
const BlockGatewayRedeDebit = {
  name: 'rede_debit',
  label: labelRedeDebit,
  content: window.wp.element.createElement(ContentRedeDebit),
  edit: window.wp.element.createElement(ContentRedeDebit),
  canMakePayment: () => true,
  ariaLabel: labelRedeDebit,
  supports: {
    features: settingsRedeDebit.supports
  }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayRedeDebit);