const settings_maxipago_debit = window.wc.wcSettings.getSetting('maxipago_debit_data', {})
const label_maxipago_debit = window.wp.htmlEntities.decodeEntities(settings_maxipago_debit.title)

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
const nonce_maxipago_debit = settings_maxipago_debit.nonce;
const Content_maxipago_Debit = (props) => {
  // Atribui o valor total da compra e transforma para float
  totalAmountString = document.querySelectorAll('.wc-block-formatted-money-amount')[1].innerHTML
  totalAmountFloat = parseFloat(totalAmountString.replace('R$ ', '').replace(',', '.'))

  const { eventRegistration, emitResponse } = props
  const { onPaymentSetup } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    maxipago_debit_card_number: '378282246310005',
    maxipago_debit_card_expiry: '01/35',
    maxipago_debit_cvc: '123',
    maxipago_debit_card_holder_name: 'Teste',
    maxipago_debit_cpf: '270.265.250-69',
    maxipago_debit_neighborhood: 'Teste bairro',
  })

  const [options, setOptions] = window.wp.element.useState([
    { key: '1', label: `1x de R$ ${totalAmountString} (à vista)` }
  ])

  const [translations, setTranslations] = window.wp.element.useState({})

  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.maxipago_debit_card_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }

  const updateCreditObject = (key, value) => {
    switch (key) {
      case 'maxipago_debit_card_expiry':
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
      case 'maxipago_debit_cvc':
        if (value.length > 4) return
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
              maxipago_debit_number: creditObject.maxipago_debit_card_number,
              maxipago_debit_expiry: creditObject.maxipago_debit_card_expiry,
              maxipago_debit_cvc: creditObject.maxipago_debit_cvc,
              maxipago_debit_holder_name: creditObject.maxipago_debit_card_holder_name,
              maxipago_debit_nonce: nonce_maxipago_debit,
              maxipago_debit_cpf: creditObject.maxipago_debit_cpf,
              billingNeighborhood: creditObject.maxipago_debit_neighborhood
            },
          },
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translations.fieldsNotFilled,
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
    translations, // Adicione translations como dependência
  ]);

  // TODO Adicionar campos de CPF e Bairro
  return (
    <>
      <wcComponents.TextInput
        id="maxipago_debit_card_number"
        label="Seu número de cartão"
        value={formatCreditCardNumber(creditObject.maxipago_debit_card_number)}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_card_number', formatCreditCardNumber(value))
        }}
      />

      <wcComponents.TextInput
        id="maxipago_debit_card_expiry"
        label="Validade do cartão"
        value={creditObject.maxipago_debit_card_expiry}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_card_expiry', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_debit_cvc"
        label="CVC"
        value={creditObject.maxipago_debit_cvc}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_cvc', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_debit_card_holder_name"
        label="Nome impresso no cartão"
        value={creditObject.maxipago_debit_card_holder_name}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_card_holder_name', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_debit_neighborhood"
        label="Bairro"
        value={creditObject.maxipago_debit_neighborhood}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_neighborhood', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_debit_cpf"
        label="CPF"
        value={formatarCPF(creditObject.maxipago_debit_cpf)}
        onChange={(value) => {
          updateCreditObject('maxipago_debit_cpf', formatarCPF(value))
        }}
      />
    </>
  )
}

const Block_Gateway_maxipago_Debit = {
  name: 'maxipago_debit',
  label: label_maxipago_debit,
  content: window.wp.element.createElement(Content_maxipago_Debit),
  edit: window.wp.element.createElement(Content_maxipago_Debit),
  canMakePayment: () => true,
  ariaLabel: label_maxipago_debit,
  supports: {
    features: settings_maxipago_debit.supports
  }
}

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway_maxipago_Debit)
