const settingsRedeCredit = window.wc.wcSettings.getSetting('rede_credit_data', {})
const labelRedeCredit = window.wp.htmlEntities.decodeEntities(settingsRedeCredit.title)
// Obtendo o nonce da variável global
const nonceRedeCredit = settingsRedeCredit.nonceRedeCredit
const translationsRedeCredit = settingsRedeCredit.translations
const minInstallmentsRede = settingsRedeCredit.minInstallmentsRede.replace(',', '.')
const ContentRedeCredit = props => {
  const totalAmountFloat = settingsRedeCredit.cartTotal
  const [selectedValue, setSelectedValue] = window.wp.element.useState('')
  const handleSortChange = event => {
    setSelectedValue(event.target.value)
    updateCreditObject('rede_credit_installments', event.target.value)
  }
  const {
    eventRegistration,
    emitResponse
  } = props
  const {
    onPaymentSetup
  } = eventRegistration
  const wcComponents = window.wc.blocksComponents
  const [creditObject, setCreditObject] = window.wp.element.useState({
    rede_credit_number: '',
    rede_credit_installments: '1',
    rede_credit_expiry: '',
    rede_credit_cvc: '',
    rede_credit_holder_name: ''
  })
  const options = []
  for (let index = 1; index <= settingsRedeCredit.maxInstallmentsRede; index++) {
    if (settingsRedeCredit[`${index}x`] !== 0) {
      options.push({
        key: index,
        label: settingsRedeCredit[`${index}x`]
      });
    } else {
      options.push({
        key: index,
        label: `${index}x de R$ ${totalAmountString}${translationsRedeCredit.interestFree}`
      });
    }
  }
  const formatCreditCardNumber = value => {
    if (value?.length > 19) return creditObject.rede_credit_number
    // Remove caracteres não numéricos
    const cleanedValue = value?.replace(/\D/g, '')
    // Adiciona espaços a cada quatro dígitos
    const formattedValue = cleanedValue?.replace(/(.{4})/g, '$1 ')?.trim()
    return formattedValue
  }
  const updateCreditObject = (key, value) => {
    let isValidDate = false
    switch (key) {
      case 'rede_credit_expiry':
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
      case 'rede_credit_cvc':
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
  window.wp.element.useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Verifica se todos os campos do creditObject estão preenchidos
      const allFieldsFilled = Object.values(creditObject).every(field => field.trim() !== '')
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
        }
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: translationsRedeCredit.fieldsNotFilled
      }
    })

    // Cancela a inscrição quando este componente é desmontado.
    return () => {
      unsubscribe()
    }
  }, [creditObject,
    // Adiciona creditObject como dependência
    emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup, translationsRedeCredit // Adicione translationsRedeCredit como dependência
  ])
  return /* #__PURE__ */React.createElement(React.Fragment, null, /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_holder_name',
    label: translationsRedeCredit.nameOnCard,
    value: creditObject.rede_credit_holder_name,
    onChange: value => {
      updateCreditObject('rede_credit_holder_name', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_number',
    label: translationsRedeCredit.cardNumber,
    value: formatCreditCardNumber(creditObject.rede_credit_number),
    onChange: value => {
      updateCreditObject('rede_credit_number', formatCreditCardNumber(value))
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_expiry',
    label: translationsRedeCredit.cardExpiringDate,
    value: creditObject.rede_credit_expiry,
    onChange: value => {
      updateCreditObject('rede_credit_expiry', value)
    }
  }), /* #__PURE__ */React.createElement(wcComponents.TextInput, {
    id: 'rede_credit_cvc',
    label: translationsRedeCredit.securityCode,
    value: creditObject.rede_credit_cvc,
    onChange: value => {
      updateCreditObject('rede_credit_cvc', value)
    }
  }), options.length > 1 && /* #__PURE__ */React.createElement(wcComponents.SortSelect, {
    instanceId: 1,
    className: 'lknIntegrationRedeForWoocommerceSelectBlocks',
    label: translationsRedeCredit.installments,
    onChange: handleSortChange,
    options,
    value: selectedValue,
    readOnly: false
  }))
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
}
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayRedeCredit)
