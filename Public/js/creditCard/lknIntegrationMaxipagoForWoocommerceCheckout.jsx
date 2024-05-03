const settingsMaxipagoCredit = window.wc.wcSettings.getSetting('maxipago_credit_data', {})
const labelMaxipagoCredit = window.wp.htmlEntities.decodeEntities(settingsMaxipagoCredit.title)

//TODO Adicionar campo de endereço manualmente aos campos de endereço
/* setTimeout(() => {
  var addressFormWrapper = document.querySelector('.wc-block-components-address-form-wrapper');
  
  // Verifica se a div foi encontrada
  if (addressFormWrapper) {
    // Cria o novo componente NovoCampoTextInput
    const novoCampo = <NovoCampoTextInput
                        id="novo_input_id"
                        label="Novo Label"
                        value={valorDoNovoInput}
                        onChange={(event) => {
                          setValorDoNovoInput(event.target.value);
                        }}
                      />;
    
    // Renderiza o novo componente dentro da div addressFormWrapper
    ReactDOM.render(novoCampo, addressFormWrapper);
  }
}, 500); */

// Obtendo o nonce da variável global
const nonceMaxipagoCredit = settingsMaxipagoCredit.nonceMaxipagoCredit;
const translationsMaxipagoCredit = settingsMaxipagoCredit.translations

const ContentMaxipagoCredit = (props) => {
  // Atribui o valor total da compra e transforma para float
  totalAmountString = document.querySelectorAll('.wc-block-formatted-money-amount')[1].innerHTML
  totalAmountFloat = parseFloat(totalAmountString.replace('R$ ', '').replace(',', '.'))

  const { eventRegistration, emitResponse } = props
  const { onPaymentSetup } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    maxipago_credit_number: '',
    maxipago_credit_installments: '1',
    maxipago_credit_expiry: '',
    maxipago_credit_cvc: '',
    maxipago_credit_holder_name: '',
    maxipago_credit_cpf: '',
    maxipago_credit_neighborhood: '',
  })

  let options = [{ key: '1', label: `1x de R$ ${totalAmountString} (à vista)` }];

  for (let index = 2; index <= settingsMaxipagoCredit.installmentsMaxipago; index++) {
    totalAmount = (totalAmountFloat / index).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    options.push({ key: index, label: `${index}x de R$ ${totalAmount}` })
  }


  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.maxipago_credit_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }

  const updateCreditObject = (key, value) => {
    switch (key) {
      case 'maxipago_credit_expiry':
        if (value.length > 7) return

        // Verifica se o valor é uma data válida (MM/YY)
        const isValidDate = /^\d{2}\/\d{2}$/.test(value)
        if (!isValidDate) {
          // Remove caracteres não numéricos
          const cleanedValue = value?.replace(/\D/g, '')
          let formattedValue = cleanedValue?.replace(/^(.{2})/, '$1 / ')?.trim()

          // Se o tamanho da string for 5, remove o espaço e a barra adicionados anteriormente
          if (formattedValue.length === 4) {
            formattedValue = formattedValue.replace(/\s\//, '')
          }

          // Atualiza o estado
          setCreditObject({
            ...creditObject,
            [key]: formattedValue
          })
        }
        return
      case 'maxipago_credit_cvc':
        if (!/^\d+$/.test(value) || value.length > 4) return
        break
      default:
        break
    }
    setCreditObject({
      ...creditObject,
      [key]: value
    })
  }

  const formatarCPF = (cpf) => {
    cpf = cpf.replace(/\D/g, ''); // Remove caracteres não numéricos
    cpf = cpf.slice(0, 11); // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os primeiros 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após os segundos 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen após os últimos 3 dígitos
    return cpf;
  }



  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos do creditObject estão preenchidos
      const allFieldsFilled = Object.values(creditObject).every((field) => field.trim() !== '');

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
              billing_cpf: creditObject.maxipago_credit_cpf,
              billing_neighborhood: creditObject.maxipago_credit_neighborhood
            },
          },
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsMaxipagoCredit.fieldsNotFilled,
      };
    });

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe();
    };
  }, [
    creditObject, // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentSetup,
    translationsMaxipagoCredit, // Adicione translationsMaxipagoCredit como dependência
  ]);

  // TODO Adicionar campos de CPF e Bairro
  return (
    <>
      <wcComponents.TextInput
        id="maxipago_credit_number"
        label={translationsMaxipagoCredit.cardNumber}
        value={formatCreditCardNumber(creditObject.maxipago_credit_number)}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_number', formatCreditCardNumber(value))
        }}
      />

      <div class="wc-block-components-text-input is-active">
        <div className="select-wrapper">
          <label htmlFor="maxipago_credit_installments" id="select-label">{translationsMaxipagoCredit.installments}</label>
          <select
            id="maxipago_credit_installments"
            value={creditObject.maxipago_credit_installments}
            onChange={(event) => {
              updateCreditObject('maxipago_credit_installments', event.target.value)
            }}
            className="wc-blocks-select" // Adicione uma classe personalizada ao select
          >
            {/* Mapeie sobre as opções para renderizá-las */}
            {options.map((option) => (
              <option key={option.key} value={option.key}>
                {option.label}
              </option>
            ))}
          </select>
        </div>
      </div>

      <wcComponents.TextInput
        id="maxipago_credit_expiry"
        label={translationsMaxipagoCredit.cardExpiringDate}
        value={creditObject.maxipago_credit_expiry}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_expiry', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_cvc"
        label={translationsMaxipagoCredit.securityCode}
        value={creditObject.maxipago_credit_cvc}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_cvc', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_holder_name"
        label={translationsMaxipagoCredit.nameOnCard}
        value={creditObject.maxipago_credit_holder_name}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_holder_name', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_neighborhood"
        label={translationsMaxipagoCredit.district}
        value={creditObject.maxipago_credit_neighborhood}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_neighborhood', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_cpf"
        label="CPF"
        value={formatarCPF(creditObject.maxipago_credit_cpf)}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_cpf', formatarCPF(value))
        }}
      />
    </>
  )
}

const Block_Gateway_maxipago = {
  name: 'maxipago_credit',
  label: labelMaxipagoCredit,
  content: window.wp.element.createElement(ContentMaxipagoCredit),
  edit: window.wp.element.createElement(ContentMaxipagoCredit),
  canMakePayment: () => true,
  ariaLabel: labelMaxipagoCredit,
  supports: {
    features: settingsMaxipagoCredit.supports
  }
}

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway_maxipago)