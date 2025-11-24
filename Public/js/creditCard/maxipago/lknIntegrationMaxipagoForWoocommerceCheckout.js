import React from 'react';
import Cards from 'react-credit-cards';
import 'react-credit-cards/es/styles-compiled.css';
const settingsMaxipagoCredit = window.wc.wcSettings.getSetting('maxipago_credit_data', {});
const labelMaxipagoCredit = window.wp.htmlEntities.decodeEntities(settingsMaxipagoCredit.title);
// Obtendo o nonce da variável global
const nonceMaxipagoCredit = settingsMaxipagoCredit.nonceMaxipagoCredit;
const translationsMaxipagoCredit = settingsMaxipagoCredit.translations;
const ContentMaxipagoCredit = props => {
  const totalAmountFloat = settingsMaxipagoCredit.cartTotal;
  const [selectedValue, setSelectedValue] = window.wp.element.useState('1');
  const handleSortChange = event => {
    const value = String(event.target.value); // Garante que seja string
    setSelectedValue(value);
    updateCreditObject('maxipago_credit_installments', value);
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
    maxipago_credit_number: '',
    maxipago_credit_installments: '1',
    maxipago_credit_expiry: '',
    maxipago_credit_cvc: '',
    maxipago_credit_holder_name: '',
    maxipago_credit_cpf: '',
    maxipago_credit_neighborhood: ''
  });
  const [focus, setFocus] = window.wp.element.useState('');
  const [options, setOptions] = window.wp.element.useState([]);

  // Função para buscar dados atualizados do backend e gerar as opções de installments (com debounce)
  let installmentTimeout = null;
  const generateMaxipagoInstallmentOptions = async () => {
    if (installmentTimeout) clearTimeout(installmentTimeout);
    installmentTimeout = setTimeout(() => {
      try {
        window.jQuery.ajax({
          url: window.ajaxurl || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'lkn_get_maxipago_credit_data'
          },
          success: function (response) {
            if (response && response.installments) {
              const newOptions = response.installments.map(installment => {
                // Extrai o texto plano do HTML (remove tags)
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = installment.label;
                const plainText = tempDiv.textContent || tempDiv.innerText || '';

                return {
                  key: installment.key,
                  label: plainText
                };
              });
              
              // Remove todas as opções atuais e adiciona as novas
              setOptions(newOptions);
              
              // Sempre garante que há uma opção selecionada válida
              const currentSelection = selectedValue || '1';
              const validOption = newOptions.find(opt => String(opt.key) === String(currentSelection));
              
              if (!validOption && newOptions.length > 0) {
                // Se a seleção atual não é válida, seleciona a primeira opção
                const firstOption = String(newOptions[0].key);
                setSelectedValue(firstOption);
                updateCreditObject('maxipago_credit_installments', firstOption);
              } else if (validOption && selectedValue !== String(validOption.key)) {
                // Se a opção é válida mas o state não está sincronizado, atualiza
                setSelectedValue(String(validOption.key));
                updateCreditObject('maxipago_credit_installments', String(validOption.key));
              }
            }
          }
        });
      } catch (error) { }
    }, 400); // 400ms de debounce
  };

  // Intercepta requisições para atualizar parcelas após mudanças no shipping
  window.wp.element.useEffect(() => {
    // Chama só uma vez ao carregar a página
    generateMaxipagoInstallmentOptions();

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
              updateCreditObject('maxipago_credit_installments', '1');
              generateMaxipagoInstallmentOptions();
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
  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.maxipago_credit_number;
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '');
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim();
    return formattedValue;
  };
  const updateCreditObject = (key, value) => {
    let isValidDate = false;
    switch (key) {
      case 'maxipago_credit_expiry':
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
      case 'maxipago_credit_cvc':
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
  const formatarCPF = cpf => {
    cpf = cpf.replace(/\D/g, ''); // Remove caracteres não numéricos
    cpf = cpf.slice(0, 11); // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os primeiros 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os segundos 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen após os últimos 3 dígitos
    return cpf;
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
              maxipago_credit_number: creditObject.maxipago_credit_number,
              maxipago_credit_installments: creditObject.maxipago_credit_installments,
              maxipago_credit_expiry: creditObject.maxipago_credit_expiry,
              maxipago_credit_cvc: creditObject.maxipago_credit_cvc,
              maxipago_credit_holder_name: creditObject.maxipago_credit_holder_name,
              maxipago_card_nonce: nonceMaxipagoCredit,
              maxipago_credit_card_cpf: creditObject.maxipago_credit_cpf,
              billing_neighborhood: creditObject.maxipago_credit_neighborhood
            }
          }
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsMaxipagoCredit.fieldsNotFilled
      };
    });

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe();
    };
  }, [creditObject,
    // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup, translationsMaxipagoCredit // Adicione translationsMaxipagoCredit como dependência
  ]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Cards, {
    number: creditObject.maxipago_credit_number,
    name: creditObject.maxipago_credit_holder_name,
    expiry: creditObject.maxipago_credit_expiry.replace(/\s+/g, ''),
    cvc: creditObject.maxipago_credit_cvc,
    placeholders: {
      name: 'NOME',
      expiry: 'MM/ANO',
      cvc: 'CVC',
      number: '•••• •••• •••• ••••'
    },
    locale: {
      valid: 'VÁLIDO ATÉ'
    },
    focused: focus
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_cpf",
    label: "CPF",
    value: formatarCPF(creditObject.maxipago_credit_cpf),
    onChange: value => {
      updateCreditObject('maxipago_credit_cpf', formatarCPF(value));
    }
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_neighborhood",
    label: translationsMaxipagoCredit.district,
    value: creditObject.maxipago_credit_neighborhood,
    onChange: value => {
      updateCreditObject('maxipago_credit_neighborhood', value);
    }
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_holder_name",
    label: translationsMaxipagoCredit.nameOnCard,
    value: creditObject.maxipago_credit_holder_name,
    maxLength: 50,
    onChange: value => {
      updateCreditObject('maxipago_credit_holder_name', value);
    },
    onFocus: () => setFocus('name')
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_number",
    label: translationsMaxipagoCredit.cardNumber,
    value: formatCreditCardNumber(creditObject.maxipago_credit_number),
    onChange: value => {
      updateCreditObject('maxipago_credit_number', formatCreditCardNumber(value));
    },
    onFocus: () => setFocus('number')
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_expiry",
    label: translationsMaxipagoCredit.cardExpiringDate,
    value: creditObject.maxipago_credit_expiry,
    onChange: value => {
      updateCreditObject('maxipago_credit_expiry', value);
    },
    onFocus: () => setFocus('expiry')
  }), /*#__PURE__*/React.createElement(wcComponents.TextInput, {
    id: "maxipago_credit_cvc",
    label: translationsMaxipagoCredit.securityCode,
    value: creditObject.maxipago_credit_cvc,
    onChange: value => {
      updateCreditObject('maxipago_credit_cvc', value);
    },
    onFocus: () => setFocus('cvc')
  }), options.length > 0 && /*#__PURE__*/React.createElement(wcComponents.SortSelect, {
    instanceId: 1,
    className: "lknIntegrationRedeForWoocommerceSelectBlocks",
    label: translationsMaxipagoCredit.installments,
    onChange: handleSortChange,
    options: options,
    value: selectedValue,
    readOnly: false
  }));
};
const BlockGatewayMaxipago = {
  name: 'maxipago_credit',
  label: labelMaxipagoCredit,
  content: window.wp.element.createElement(ContentMaxipagoCredit),
  edit: window.wp.element.createElement(ContentMaxipagoCredit),
  canMakePayment: () => true,
  ariaLabel: labelMaxipagoCredit,
  supports: {
    features: settingsMaxipagoCredit.supports
  }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayMaxipago);