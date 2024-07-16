const settingsRedeDebit = window.wc.wcSettings.getSetting('rede_debit_data', {})
const labelRedeDebit = window.wp.htmlEntities.decodeEntities(settingsRedeDebit.title)
// Obtendo o nonce da variável global
const nonceRedeDebit = settingsRedeDebit.nonceRedeDebit
const translationsRedeDebit = settingsRedeDebit.translations

const ContentRedeDebit = (props) => {
  const { eventRegistration, emitResponse } = props
  const { onPaymentSetup } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [debitObject, setDebitObject] = window.wp.element.useState({
    rede_debit_number: '',
    rede_debit_installments: '1',
    rede_debit_expiry: '',
    rede_debit_cvc: '',
    rede_debit_holder_name: ''
  })

  const formatDebitCardNumber = value => {
    if (value?.length > 19) return debitObject.rede_debit_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }

  const updateDebitObject = (key, value) => {
    let isValidDate = false

    switch (key) {
      case 'rede_debit_expiry':
        if (value.length > 7) return

        // Verifica se o valor é uma data válida (MM/YY)
        isValidDate = /^\d{2}\/\d{2}$/.test(value)
        if (!isValidDate) {
          // Remove caracteres não numéricos
          const cleanedValue = value?.replace(/\D/g, '')
          let formattedValue = cleanedValue?.replace(/^(.{2})/, '$1 / ')?.trim()

          // Se o tamanho da string for 5, remove o espaço e a barra adicionados anteriormente
          if (formattedValue.length === 4) {
            formattedValue = formattedValue.replace(/\s\//, '')
          }

          // Atualiza o estado
          setDebitObject({
            ...debitObject,
            [key]: formattedValue
          })
        }
        return
      case 'rede_debit_cvc':
        if ((!/^\d+$/.test(value) && value !== '') || value.length > 4) return
        break
      default:
        break
    }
    setDebitObject({
      ...debitObject,
      [key]: value
    })
  }

  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos do debitObject estão preenchidos
      const allFieldsFilled = Object.values(debitObject).every((field) => field.trim() !== '')

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
              rede_card_nonce: nonceRedeDebit
            }
          }
        }
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsRedeDebit.fieldsNotFilled
      }
    })

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe()
    }
  }, [
    debitObject, // Adiciona debitObject como dependência
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentSetup,
    translationsRedeDebit // Adicione translationsRedeDebit como dependência
  ])

  return (
    <>
      <wcComponents.TextInput
        id="rede_debit_holder_name"
        label={translationsRedeDebit.nameOnCard}
        value={debitObject.rede_debit_holder_name}
        onChange={(value) => {
          updateDebitObject('rede_debit_holder_name', value)
        }}
      />

      <wcComponents.TextInput
        id="rede_debit_number"
        label={translationsRedeDebit.cardNumber}
        value={formatDebitCardNumber(debitObject.rede_debit_number)}
        onChange={(value) => {
          updateDebitObject('rede_debit_number', formatDebitCardNumber(value))
        }}
      />

      <wcComponents.TextInput
        id="rede_debit_expiry"
        label={translationsRedeDebit.cardExpiringDate}
        value={debitObject.rede_debit_expiry}
        onChange={(value) => {
          updateDebitObject('rede_debit_expiry', value)
        }}
      />

      <wcComponents.TextInput
        id="rede_debit_cvc"
        label={translationsRedeDebit.securityCode}
        value={debitObject.rede_debit_cvc}
        onChange={(value) => {
          updateDebitObject('rede_debit_cvc', value)
        }}
      />
    </>
  )
}

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
}

window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayRedeDebit)
