const settings_rede_debit = window.wc.wcSettings.getSetting('rede_credit_data', {})
const label_rede_debit = window.wp.htmlEntities.decodeEntities(settings_rede_debit.title)
// Obtendo o nonce da variável global
const nonce_rede_debit = window.redeNonce;

const Content_rede_debit = (props) => {
  // Atribui o valor total da compra e transforma para float
  totalAmountString = document.querySelectorAll('.wc-block-formatted-money-amount')[1].innerHTML
  totalAmountFloat = parseFloat(totalAmountString.replace('R$ ', '').replace(',', '.'))

  const { eventRegistration, emitResponse } = props
  const { onPaymentSetup } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    rede_credit_number: '',
    rede_credit_installments: '1',
    rede_credit_expiry: '',
    rede_credit_cvc: '',
    rede_credit_holder_name: '',
  })

  const [options, setOptions] = window.wp.element.useState([
    { key: '1', label: `1x de R$ ${totalAmountString} (à vista)` }
  ])

  const [translations, setTranslations] = window.wp.element.useState({})

  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.rede_credit_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }

  const updateCreditObject = (key, value) => {
    switch (key) {
      case 'rede_credit_expiry':
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
      case 'rede_credit_cvc':
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

        for (let index = 2; index <= jsonData.installments_rede; index++) {
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
              rede_credit_number: creditObject.rede_credit_number,
              rede_credit_installments: creditObject.rede_credit_installments,
              rede_credit_expiry: creditObject.rede_credit_expiry,
              rede_credit_cvc: creditObject.rede_credit_cvc,
              rede_credit_holder_name: creditObject.rede_credit_holder_name,
              rede_card_nonce: nonce_rede_debit
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


  return (
    <>
      <wcComponents.TextInput
        id="rede_credit_number"
        label="Seu número de cartão"
        value={formatCreditCardNumber(creditObject.rede_credit_number)}
        onChange={(value) => {
          updateCreditObject('rede_credit_number', formatCreditCardNumber(value))
        }}
      />

      <div class="wc-block-components-text-input is-active">
        <div className="select-wrapper">
          <label htmlFor="rede_credit_installments" id="select-label">Número de Parcelas:</label>
          <select
            id="rede_credit_installments"
            value={creditObject.rede_credit_installments}
            onChange={(event) => {
              updateCreditObject('rede_credit_installments', event.target.value)
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
        id="rede_credit_expiry"
        label="Validade do cartão"
        value={creditObject.rede_credit_expiry}
        onChange={(value) => {
          updateCreditObject('rede_credit_expiry', value)
        }}
      />

      <wcComponents.TextInput
        id="rede_credit_cvc"
        label="CVC"
        value={creditObject.rede_credit_cvc}
        onChange={(value) => {
          updateCreditObject('rede_credit_cvc', value)
        }}
      />

      <wcComponents.TextInput
        id="rede_credit_holder_name"
        label="Nome impresso no cartão"
        value={creditObject.rede_credit_holder_name}
        onChange={(value) => {
          updateCreditObject('rede_credit_holder_name', value)
        }}
      />
    </>
  )
}

const Block_Gateway_rede_debit = {
  name: 'rede_credit',
  label: label_rede_debit,
  content: window.wp.element.createElement(Content_rede_debit),
  edit: window.wp.element.createElement(Content_rede_debit),
  canMakePayment: () => true,
  ariaLabel: label_rede_debit,
  supports: {
    features: settings_rede_debit.supports
  }
}

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway_rede_debit)