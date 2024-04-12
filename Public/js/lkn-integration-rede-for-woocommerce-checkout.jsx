const settings = window.wc.wcSettings.getSetting('rede_credit_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('My Custom Gateway', 'rede_credit');

const Content = props => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;
    const wcComponents = window.wc.blocksComponents
    let [creditObject, setCreditObject] = window.wp.element.useState({
        rede_credit_number: "",
        rede_credit_installments: "",
        rede_credit_expiry: "",
        rede_credit_cvc: "",
        rede_credit_holder_name: "" //TODO terminar validações antes de enviar para o backend
    });

    window.wp.element.useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            // Here we can do any processing we need, and then emit a response.
            // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.
            const myGatewayCustomData = '12345';
            const customDataIsValid = !!myGatewayCustomData.length;
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
                };
            }
            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'There was an error'
            };
        });
        // Unsubscribes when this component is unmounted.
        return () => {
            unsubscribe();
        }
    }, [emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentProcessing]
    );



    return (
        <>
            <wcComponents.TextInput
                id="rede_credit_number"
                label="Seu número de cartão"
                value={creditObject.rede_credit_number}
                onChange={(value) => {
                    setCreditObject({
                        ...creditObject,
                        rede_credit_number: value
                    })
                }}
            />

            <wcComponents.TextInput
                id="rede_credit_installments"
                label="Parcelas"
                value={creditObject.rede_credit_installments}
                onChange={(value) => {
                    setCreditObject({
                        ...creditObject,
                        rede_credit_installments: value
                    })
                }}
            />

            <wcComponents.TextInput
                id="rede_credit_expiry"
                label="Validade do cartão"
                value={creditObject.rede_credit_expiry}
                onChange={(value) => {
                    setCreditObject({
                        ...creditObject,
                        rede_credit_expiry: value
                    })
                }}
            />

            <wcComponents.TextInput
                id="rede_credit_cvc"
                label="CVC"
                value={creditObject.rede_credit_cvc}
                onChange={(value) => {
                    setCreditObject({
                        ...creditObject,
                        rede_credit_cvc: value
                    })
                }}
            />

            <wcComponents.TextInput
                id="rede_credit_holder_name"
                label="Nome impresso no cartão"
                value={creditObject.rede_credit_holder_name}
                onChange={(value) => {
                    setCreditObject({
                        ...creditObject,
                        rede_credit_holder_name: value
                    })
                }}
            />
        </>
    );
};

const Block_Gateway = {
    name: 'rede_credit',
    label: label,
    content: window.wp.element.createElement(Content),
    edit: window.wp.element.createElement(Content),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
};

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);

