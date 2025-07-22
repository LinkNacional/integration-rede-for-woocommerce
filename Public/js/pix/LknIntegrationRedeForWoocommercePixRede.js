const settingsIntegrationRedePix = window.wc.wcSettings.getSetting('integration_rede_pix_data', {});
const labelIntegrationRedePix = window.wp.htmlEntities.decodeEntities(settingsIntegrationRedePix.title);
const ContentIntegrationRedePix = props => {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center'
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "LknIntegrationRedeForWoocommercePaymentFields"
  }, /*#__PURE__*/React.createElement("p", null, "Pay for your purchase with a pix through"), /*#__PURE__*/React.createElement("svg", {
    id: "integration-logo-rede",
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 480.72 156.96"
  }, /*#__PURE__*/React.createElement("title", null, "logo-rede"), /*#__PURE__*/React.createElement("path", {
    className: "cls-1",
    style: {
      fill: 'rgb(255, 120, 0)'
    },
    d: "M475.56 98.71h-106c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S400.6 21.4 378.27 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51h-29.89V0H263v60.91h-31.23c-29.94.15-46.61 15.31-48.79 37.8h-52.26c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S161.8 21.4 139.47 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51H57.47c-13.74 0-25.93 4.22-32.64 12.5V62.78H0v87.62c0 5 1.56 6.56 6.4 6.56h12.5c4.68 0 6.4-1.56 6.4-6.56v-34.51c0-26.08 16.4-31.24 33.27-31.24h21.06c5.26 25.88 26.93 38.26 52 38.26h54.48c6.26 15 21.21 22.8 45.17 22.8h14.52c23.74 0 43.73-16.87 43.73-41.7V84.65h28.87c5.26 25.88 26.93 38.26 52 38.26h105.16a5.23 5.23 0 0 0 5.15-5.31v-13.9a5.07 5.07 0 0 0-5.15-4.99zM127.91 45.14h12.34c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.07-8.84 7.88-15.81 22.56-15.81zM263 104.8c0 9.84-7.49 16.87-17.18 16.87h-16.24c-13.12 0-21.71-5.15-21.71-18.12 0-12.65 8.59-18.9 21.71-18.9H263v20.15zm103.71-59.66H379c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.12-8.84 7.9-15.81 22.61-15.81z"
  }))), /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "wc-block-components-button wp-element-button wc-block-components-checkout-place-order-button contained",
    onClick: () => {
      var _buttons;
      const buttons = document.querySelectorAll('.wc-block-components-button.wp-element-button.wc-block-components-checkout-place-order-button.contained');
      (_buttons = buttons[buttons.length - 1]) === null || _buttons === void 0 || _buttons.click();
    },
    style: {
      padding: '8px 21px',
      borderRadius: '4px',
      backgroundColor: '#002c4d',
      borderColor: '#4db6ac',
      borderWidth: '1px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "wc-block-components-button__text",
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '10px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "wc-block-components-checkout-place-order-button__text"
  }, "Gerar Pix"), /*#__PURE__*/React.createElement("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    x: "0px",
    y: "0px",
    width: "32",
    height: "48",
    viewBox: "0 0 48 48"
  }, /*#__PURE__*/React.createElement("path", {
    fill: "#4db6ac",
    d: "M11.9,12h-0.68l8.04-8.04c2.62-2.61,6.86-2.61,9.48,0L36.78,12H36.1c-1.6,0-3.11,0.62-4.24,1.76\tl-6.8,6.77c-0.59,0.59-1.53,0.59-2.12,0l-6.8-6.77C15.01,12.62,13.5,12,11.9,12z"
  }), /*#__PURE__*/React.createElement("path", {
    fill: "#4db6ac",
    d: "M36.1,36h0.68l-8.04,8.04c-2.62,2.61-6.86,2.61-9.48,0L11.22,36h0.68c1.6,0,3.11-0.62,4.24-1.76\tl6.8-6.77c0.59-0.59,1.53-0.59,2.12,0l6.8,6.77C32.99,35.38,34.5,36,36.1,36z"
  }), /*#__PURE__*/React.createElement("path", {
    fill: "#4db6ac",
    d: "M44.04,28.74L38.78,34H36.1c-1.07,0-2.07-0.42-2.83-1.17l-6.8-6.78c-1.36-1.36-3.58-1.36-4.94,0\tl-6.8,6.78C13.97,33.58,12.97,34,11.9,34H9.22l-5.26-5.26c-2.61-2.62-2.61-6.86,0-9.48L9.22,14h2.68c1.07,0,2.07,0.42,2.83,1.17\tl6.8,6.78c0.68,0.68,1.58,1.02,2.47,1.02s1.79-0.34,2.47-1.02l6.8-6.78C34.03,14.42,35.03,14,36.1,14h2.68l5.26,5.26\tC46.65,21.88,46.65,26.12,44.04,28.74z"
  })))));
};
const BlockGatewayIntegrationRedePix = {
  name: 'integration_rede_pix',
  label: labelIntegrationRedePix,
  content: /*#__PURE__*/React.createElement(ContentIntegrationRedePix, null),
  edit: /*#__PURE__*/React.createElement(ContentIntegrationRedePix, null),
  canMakePayment: () => true,
  ariaLabel: labelIntegrationRedePix,
  supports: {
    features: settingsIntegrationRedePix.supports
  }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayIntegrationRedePix);