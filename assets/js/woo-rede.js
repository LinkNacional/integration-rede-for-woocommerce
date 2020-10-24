jQuery(document).ready(function () {
	jQuery('body')
		.on('init init_checkout updated_checkout payment_method_selected checkout_error update_checkout updated_wc_div', function () {

			jQuery('.woocommerce .woocommerce-checkout').card({
				container: '#rede-card-animation',

				/**
				 * Selectors
				 */
				formSelectors: {
					numberInput: '#rede-card-number',
					nameInput: '#rede-card-holder-name',
					expiryInput: '#rede-card-expiry',
					cvcInput: '#rede-card-cvc',
				},

				/**
				 * Placeholders
				 */
				placeholders: {
					number: '•••• •••• •••• ••••',
					name: 'NOME',
					expiry: 'MM/ANO',
					cvc: 'CVC'
				},

				/**
				 * Translation Brazilian Portuguese
				 */
				messages: {
					validDate: 'VALIDADE',
					monthYear: ''
				},

				/**
				 * Debug
				 */
				debug: !!window.wooRede,
			});

		});
});
