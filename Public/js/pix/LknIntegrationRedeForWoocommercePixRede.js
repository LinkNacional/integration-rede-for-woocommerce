const settingsIntegrationRedePix = window.wc.wcSettings.getSetting('integration_rede_pix_data', {})
const labelIntegrationRedePix = window.wp.htmlEntities.decodeEntities(settingsIntegrationRedePix.title)
const ContentIntegrationRedePix = props => {
  return /* #__PURE__ */React.createElement('div', {
    class: 'LknIntegrationRedeForWoocommercePaymentFields'
  }, /* #__PURE__ */React.createElement('p', null, settingsIntegrationRedePix.description), /* #__PURE__ */React.createElement('svg', {
    id: 'integration-logo-rede',
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 480.72 156.96'
  }, /* #__PURE__ */React.createElement('title', null, 'logo-rede'), /* #__PURE__ */React.createElement('path', {
    style: {
      fill: '#ff7800'
    },
    class: 'cls-1',
    d: 'M475.56 98.71h-106c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S400.6 21.4 378.27 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51h-29.89V0H263v60.91h-31.23c-29.94.15-46.61 15.31-48.79 37.8h-52.26c-15.45 0-22-6-24.67-14.05h33.41c22.33 0 36.08-9.84 36.08-31.08S161.8 21.4 139.47 21.4h-10.62c-20 0-44.34 11.64-49.45 39.51H57.47c-13.74 0-25.93 4.22-32.64 12.5V62.78H0v87.62c0 5 1.56 6.56 6.4 6.56h12.5c4.68 0 6.4-1.56 6.4-6.56v-34.51c0-26.08 16.4-31.24 33.27-31.24h21.06c5.26 25.88 26.93 38.26 52 38.26h54.48c6.26 15 21.21 22.8 45.17 22.8h14.52c23.74 0 43.73-16.87 43.73-41.7V84.65h28.87c5.26 25.88 26.93 38.26 52 38.26h105.16a5.23 5.23 0 0 0 5.15-5.31v-13.9a5.07 5.07 0 0 0-5.15-4.99zM127.91 45.14h12.34c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.07-8.84 7.88-15.81 22.56-15.81zM263 104.8c0 9.84-7.49 16.87-17.18 16.87h-16.24c-13.12 0-21.71-5.15-21.71-18.12 0-12.65 8.59-18.9 21.71-18.9H263v20.15zm103.71-59.66H379c5.62 0 9.53 2.34 9.53 8 0 5.31-3.9 7.81-9.53 7.81h-34.9c2.12-8.84 7.9-15.81 22.61-15.81z'
  })))
}
const BlockGatewayIntegrationRedePix = {
  name: 'integration_rede_pix',
  label: labelIntegrationRedePix,
  content: window.wp.element.createElement(ContentIntegrationRedePix),
  edit: window.wp.element.createElement(ContentIntegrationRedePix),
  canMakePayment: () => true,
  ariaLabel: labelIntegrationRedePix,
  supports: {
    features: settingsIntegrationRedePix.supports
  }
}
window.wc.wcBlocksRegistry.registerPaymentMethod(BlockGatewayIntegrationRedePix)
