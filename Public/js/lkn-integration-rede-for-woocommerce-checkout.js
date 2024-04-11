const settings = window.wc.wcSettings.getSetting('rede_credit_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('My Custom Gateway', 'rede_credit');
const Content = () => {
  let [testeCardValue, setTesteCardValue] = window.wp.element.useState("");
  const handleTesteCardChange = event => {
    const newValue = event.target.value;
    console.log(newValue);
    console.log(newValue.includes(' '));
    if (isNaN(newValue) || newValue.includes(' ')) return;
    setTesteCardValue(newValue);
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "wc-block-components-text-input wc-block-components-address-form__teste_card is-active"
  }, /*#__PURE__*/React.createElement("input", {
    type: "text",
    id: "billing-teste_card",
    autocapitalize: "sentences",
    autocomplete: "given-card",
    "aria-label": "Cart\xE3o",
    required: "",
    "aria-invalid": "false",
    title: "",
    value: testeCardValue,
    onChange: handleTesteCardChange
  }), /*#__PURE__*/React.createElement("label", {
    htmlFor: "billing-teste_card"
  }, "Cart\xE3o")), /*#__PURE__*/React.createElement("div", {
    class: "form-row form-row-wide rede-card"
  }, /*#__PURE__*/React.createElement("input", {
    id: "rede-card-number",
    name: "rede_credit_number",
    class: "input-text jp-card-invalid wc-credit-card-form-card-number",
    type: "tel",
    maxlength: "22",
    autocomplete: "off",
    style: {
      fontSize: '1.5em',
      padding: '8px 45px'
    }
  })));
};
const Block_Gateway = {
  name: 'rede_credit',
  label: label,
  content: window.wp.element.createElement(Content),
  edit: window.wp.element.createElement(Content),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
console.log(Block_Gateway);
