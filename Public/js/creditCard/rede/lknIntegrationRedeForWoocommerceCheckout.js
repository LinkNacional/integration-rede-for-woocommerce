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
  const [selectedValue, setSelectedValue] = window.wp.element.useState('');
  const handleSortChange = event => {
    setSelectedValue(event.target.value);
    updateCreditObject('rede_credit_installments', event.target.value);
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

  // Função para buscar dados atualizados do backend e gerar as opções de installments
  const generateInstallmentOptions = async () => {
    try {
      window.jQuery.ajax({
        url: window.ajaxurl || '/wp-admin/admin-ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'lkn_get_rede_credit_data'
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
            setOptions(plainOptions);
          }
        },
        error: function () {
          // Se falhar, mantém as opções atuais
        }
      });
    } catch (error) {
      // Se falhar, mantém as opções atuais
    }
  };

  // Intercepta window.fetch para WooCommerce Blocks e atualiza parcelas após requisições relevantes
  window.wp.element.useEffect(() => {
    generateInstallmentOptions();
    // Intercepta fetch
    const originalFetch = window.fetch;
    window.fetch = async function (...args) {
      const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url || '');
      const isWooBlocksRequest = url.includes('/wp-json/wc/store/v1/batch') || url.includes('/wp-json/wc/store/v1/cart/select-shipping-rate') || url.includes('/wp-json/wc/store/v1/cart');
      const response = await originalFetch.apply(this, args);
      if (isWooBlocksRequest) {
        response.clone().json().then(() => {
          generateInstallmentOptions();
        }).catch(() => {
          generateInstallmentOptions();
        });
      }
      return response;
    };

    // Intercepta XMLHttpRequest
    const originalOpen = window.XMLHttpRequest.prototype.open;
    window.XMLHttpRequest.prototype.open = function (...args) {
      this._url = args[1];
      return originalOpen.apply(this, args);
    };
    const originalSend = window.XMLHttpRequest.prototype.send;
    window.XMLHttpRequest.prototype.send = function (...args) {
      this.addEventListener('load', function () {
        if (this._url && (
          this._url.includes('/wp-json/wc/store/v1/batch') ||
          this._url.includes('/wp-json/wc/store/v1/cart/select-shipping-rate')
        )) {
          generateInstallmentOptions();
        }
      });
      return originalSend.apply(this, args);
    };

    return () => {
      window.fetch = originalFetch;
      window.XMLHttpRequest.prototype.open = originalOpen;
      window.XMLHttpRequest.prototype.send = originalSend;
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
      const allFieldsFilled = Object.values(creditObject).every(field => field.trim() !== '');
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
      {options.length > 1 && (
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