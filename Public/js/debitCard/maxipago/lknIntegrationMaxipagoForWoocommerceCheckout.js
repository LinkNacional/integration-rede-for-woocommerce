const settingsMaxipagoDebit = window.wc.wcSettings.getSetting('maxipago_debit_data', {})
const labelMaxipagoDebit = window.wp.htmlEntities.decodeEntities(settingsMaxipagoDebit.title)

// Obtendo o nonce da variável global
const nonceMaxipagoDebit = settingsMaxipagoDebit.nonceMaxipagoDebit
const translationsMaxipagoDebit = settingsMaxipagoDebit.translations
const ContentMaxipagoDebit = props => {
  const {
    eventRegistration,
    emitResponse
  } = props
  const {
    onPaymentSetup
  } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    maxipago_debit_card_number: '',
    maxipago_debit_card_expiry: '',
    maxipago_debit_cvc: '',
    maxipago_debit_card_holder_name: '',
    maxipago_debit_cpf: '',
    maxipago_debit_neighborhood: ''
  })
  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.maxipago_debit_card_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }
  const updateCreditObject = (key, value) => {
    let isValidDate = false
    switch (key) {
      case 'maxipago_debit_card_expiry':
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
      case 'maxipago_debit_cvc':
        if (!/^\d+$/.test(value) && value !== '' || value.length > 4) return
        break
      default:
        break
    }
    setCreditObject({
      ...creditObject,
      [key]: value
    })
  }
  const formatarCPF = cpf => {
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
      const allFieldsFilled = Object.values(creditObject).every(field => field.trim() !== '')
      if (allFieldsFilled) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              maxipago_debit_number: creditObject.maxipago_debit_card_number,
              maxipago_debit_expiry: creditObject.maxipago_debit_card_expiry,
              maxipago_debit_cvc: creditObject.maxipago_debit_cvc,
              maxipago_debit_holder_name: creditObject.maxipago_debit_card_holder_name,
              maxipago_debit_nonce: nonceMaxipagoDebit,
              maxipago_debit_cpf: creditObject.maxipago_debit_cpf,
              billingNeighborhood: creditObject.maxipago_debit_neighborhood
            }
          }
        }
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsMaxipagoDebit.fieldsNotFilled
      }
    })

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe()
    }
  }, [creditObject,
  // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup, translationsMaxipagoDebit // Adicione translations como dependência
  ])
  return /* #__PURE__ */React.createElement(React.Fragment, null, /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_cpf',
    label: 'CPF',
    value: formatarCPF(creditObject.maxipago_debit_cpf),
    onChange: value => {
      updateCreditObject('maxipago_debit_cpf', formatarCPF(value))
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_neighborhood',
    label: translationsMaxipagoDebit.district,
    value: creditObject.maxipago_debit_neighborhood,
    onChange: value => {
      updateCreditObject('maxipago_debit_neighborhood', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_card_holder_name',
    label: translationsMaxipagoDebit.nameOnCard,
    value: creditObject.maxipago_debit_card_holder_name,
    onChange: value => {
      updateCreditObject('maxipago_debit_card_holder_name', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_card_number',
    label: translationsMaxipagoDebit.cardNumber,
    value: formatCreditCardNumber(creditObject.maxipago_debit_card_number),
    onChange: value => {
      updateCreditObject('maxipago_debit_card_number', formatCreditCardNumber(value))
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_card_expiry',
    label: translationsMaxipagoDebit.cardExpiringDate,
    value: creditObject.maxipago_debit_card_expiry,
    onChange: value => {
      updateCreditObject('maxipago_debit_card_expiry', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'maxipago_debit_cvc',
    label: translationsMaxipagoDebit.securityCode,
    value: creditObject.maxipago_debit_cvc,
    onChange: value => {
      updateCreditObject('maxipago_debit_cvc', value)
    }
  }))
}
const BlockGatewayMaxipagoDebit = {
  name: 'maxipago_debit',
  label: labelMaxipagoDebit,
  content: window.wp.element.createElement(ContentMaxipagoDebit),
  edit: window.wp.element.createElement(ContentMaxipagoDebit),
  canMakePayment: () => true,
  ariaLabel: labelMaxipagoDebit,
  supports: {
    features: settingsMaxipagoDebit.supports
  }
}
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayMaxipagoDebit)
