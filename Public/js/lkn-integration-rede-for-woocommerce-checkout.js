const settings = window.wc.wcSettings.getSetting('rede_credit_data', {})
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('My Custom Gateway', 'rede_credit')
const Content = props => {
  const {
    eventRegistration,
    emitResponse
  } = props
  const {
    onPaymentProcessing
  } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    rede_credit_number: '',
    rede_credit_installments: '',
    rede_credit_expiry: '',
    rede_credit_cvc: '',
    rede_credit_holder_name: '' // TODO terminar validações antes de enviar para o backend
  })

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
  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentProcessing(async () => {
      // Aqui podemos fazer qualquer processamento necessário e, em seguida, emitir uma resposta.
      // Por exemplo, podemos validar um campo personalizado ou realizar uma solicitação AJAX e, em seguida, emitir uma resposta indicando se é válido ou não.
      const myGatewayCustomData = '12345'
      const customDataIsValid = !!myGatewayCustomData.length
      if (customDataIsValid) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              rede_credit_number: creditObject.rede_credit_number,
              rede_credit_installments: creditObject.rede_credit_installments,
              rede_credit_expiry: creditObject.rede_credit_expiry,
              rede_credit_cvc: creditObject.rede_credit_cvc,
              rede_credit_holder_name: creditObject.rede_credit_holder_name
            }
          }
        }
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: 'Houve um erro'
      }
    })
    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe()
    }
  }, [emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentProcessing])
  return /* #__PURE__ */React.createElement(React.Fragment, null, /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_number',
    label: 'Seu n\xFAmero de cart\xE3o',
    value: formatCreditCardNumber(creditObject.rede_credit_number),
    onChange: value => {
      updateCreditObject('rede_credit_number', formatCreditCardNumber(value))
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_installments',
    label: 'Parcelas',
    value: creditObject.rede_credit_installments,
    onChange: value => {
      updateCreditObject('rede_credit_installments', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_expiry',
    label: 'Validade do cart\xE3o',
    value: creditObject.rede_credit_expiry,
    onChange: value => {
      updateCreditObject('rede_credit_expiry', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_cvc',
    label: 'CVC',
    value: creditObject.rede_credit_cvc,
    onChange: value => {
      updateCreditObject('rede_credit_cvc', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_holder_name',
    label: 'Nome impresso no cart\xE3o',
    value: creditObject.rede_credit_holder_name,
    onChange: value => {
      updateCreditObject('rede_credit_holder_name', value)
    }
  }))
}
const Block_Gateway = {
  name: 'rede_credit',
  label,
  content: window.wp.element.createElement(Content),
  edit: window.wp.element.createElement(Content),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
}
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway)
