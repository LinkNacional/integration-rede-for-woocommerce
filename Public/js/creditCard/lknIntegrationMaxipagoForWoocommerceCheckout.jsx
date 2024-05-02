const settings_maxipago = window.wc.wcSettings.getSetting('maxipago_credit_data', {})
const label_maxipago = window.wp.htmlEntities.decodeEntities(settings_maxipago.title)

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
const nonce_maxipago = window.maxipagoNonce;

const Content_maxipago = (props) => {
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

  const [options, setOptions] = window.wp.element.useState([
    { key: '1', label: `1x de R$ ${totalAmountString} (à vista)` }
  ])

  const [translations, setTranslations] = window.wp.element.useState({})

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

  // Requisição para obter o número máximo de parcelas e traduções
  window.wp.element.useEffect(() => {
    fetch(`${window.location.origin}/wp-json/redeForWoocommerce/getphpAttributes`)
      .then(response => {
        return response.text()
      }).then(text => {
        const jsonStartIndex = text.indexOf('{') // Encontra o índice do primeiro '{'
        const jsonString = text.slice(jsonStartIndex) // Pega o texto a partir desse índice
        const jsonData = JSON.parse(jsonString) // Analisa o JSON

        //Atribui traduções do backend
        setTranslations(jsonData.translations)

        for (let index = 2; index <= jsonData.installments_maxipago; index++) {
          totalAmount = (totalAmountFloat / index).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          })

          setOptions(prevOptions => [
            ...prevOptions,
            { key: index, label: `${index}x de R$ ${totalAmount}` }
          ])
        }
      }).catch(error => {
        console.log('Ocorreu um erro:', error)
      })
  }, [])

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
              maxipago_card_nonce: nonce_maxipago,
              billing_cpf: creditObject.maxipago_credit_cpf,
              billing_neighborhood: creditObject.maxipago_credit_neighborhood
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
        id="maxipago_credit_number"
        label="Seu número de cartão"
        value={formatCreditCardNumber(creditObject.maxipago_credit_number)}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_number', formatCreditCardNumber(value))
        }}
      />

      <div class="wc-block-components-text-input is-active">
        <div className="select-wrapper">
          <label htmlFor="maxipago_credit_installments" id="select-label">Número de Parcelas:</label>
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
        label="Validade do cartão"
        value={creditObject.maxipago_credit_expiry}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_expiry', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_cvc"
        label="CVC"
        value={creditObject.maxipago_credit_cvc}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_cvc', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_holder_name"
        label="Nome impresso no cartão"
        value={creditObject.maxipago_credit_holder_name}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_holder_name', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_neighborhood"
        label="Bairro"
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
  label: label_maxipago,
  content: window.wp.element.createElement(Content_maxipago),
  edit: window.wp.element.createElement(Content_maxipago),
  canMakePayment: () => true,
  ariaLabel: label_maxipago,
  supports: {
    features: settings_maxipago.supports
  }
}

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway_maxipago)
