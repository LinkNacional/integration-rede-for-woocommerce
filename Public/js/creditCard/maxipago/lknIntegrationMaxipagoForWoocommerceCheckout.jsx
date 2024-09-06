const settingsMaxipagoCredit = window.wc.wcSettings.getSetting('maxipago_credit_data', {})
const labelMaxipagoCredit = window.wp.htmlEntities.decodeEntities(settingsMaxipagoCredit.title)
// Obtendo o nonce da variável global
const nonceMaxipagoCredit = settingsMaxipagoCredit.nonceMaxipagoCredit
const translationsMaxipagoCredit = settingsMaxipagoCredit.translations
const minInstallmentsMaxipago = settingsMaxipagoCredit.minInstallmentsMaxipago.replace(',', '.')
const ContentMaxipagoCredit = (props) => {
  const totalAmountFloat = settingsMaxipagoCredit.cartTotal

  const [selectedValue, setSelectedValue] = window.wp.element.useState('')

  const handleSortChange = (event) => {
    setSelectedValue(event.target.value)
    updateCreditObject('maxipago_credit_installments', event.target.value)
  }

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
    maxipago_credit_neighborhood: ''
  })

  const options = []

  for (let index = 1; index <= settingsMaxipagoCredit.maxInstallmentsMaxipago; index++) {
    
    if (settingsMaxipagoCredit[`${index}x`] !== 0) {
      options.push({ key: index, label: settingsMaxipagoCredit[`${index}x`] })
    } else {
      options.push({ key: index, label: `${index}x de R$ ${totalAmountString}${translationsMaxipagoCredit.interestFree}` })
    }
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
    let isValidDate = false

    switch (key) {
      case 'maxipago_credit_expiry':
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
          setCreditObject({
            ...creditObject,
            [key]: formattedValue
          })
        }
        return
      case 'maxipago_credit_cvc':
        if ((!/^\d+$/.test(value) && value !== '') || value.length > 4) return
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
    cpf = cpf.replace(/\D/g, '') // Remove caracteres não numéricos
    cpf = cpf.slice(0, 11) // Limita o CPF ao máximo de 11 caracteres (o máximo de caracteres para um CPF)
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2') // Adiciona ponto após os primeiros 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2') // Adiciona ponto após os segundos 3 dígitos
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2') // Adiciona hífen após os últimos 3 dígitos
    return cpf
  }

  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos do creditObject estão preenchidos
      const allFieldsFilled = Object.values(creditObject).every((field) => field.trim() !== '')

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
        }
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsMaxipagoCredit.fieldsNotFilled
      }
    })

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe()
    }
  }, [
    creditObject, // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentSetup,
    translationsMaxipagoCredit // Adicione translationsMaxipagoCredit como dependência
  ])

  return (
    <>
      <wcComponents.TextInput
        id="maxipago_credit_cpf"
        label="CPF"
        value={formatarCPF(creditObject.maxipago_credit_cpf)}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_cpf', formatarCPF(value))
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
        id="maxipago_credit_holder_name"
        label={translationsMaxipagoCredit.nameOnCard}
        value={creditObject.maxipago_credit_holder_name}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_holder_name', value)
        }}
      />

      <wcComponents.TextInput
        id="maxipago_credit_number"
        label={translationsMaxipagoCredit.cardNumber}
        value={formatCreditCardNumber(creditObject.maxipago_credit_number)}
        onChange={(value) => {
          updateCreditObject('maxipago_credit_number', formatCreditCardNumber(value))
        }}
      />

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

      {options.length > 1 && (
        <wcComponents.SortSelect
          instanceId={1}
          className="lknIntegrationRedeForWoocommerceSelectBlocks"
          label={translationsMaxipagoCredit.installments}
          onChange={handleSortChange}
          options={options}
          value={selectedValue}
          readOnly={false}
        />
      )}

    </>
  )
}

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
}

window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayMaxipago)
