window.jQuery(function ($) {
	const $form = $('.woocommerce .woocommerce-checkout');
	const inputSelectors = {
		numberInput: '#rede-card-number',
		nameInput: '#rede-card-holder-name',
		expiryInput: '#rede-card-expiry',
		cvcInput: '#rede-card-cvc',
	};

	$(document.body).on('updated_checkout wc-credit-card-form-init', function (e) {
		// maybe delete old card data
		$form.data('card', null)

		// init animated card
		$form.card({
			container: '#rede-card-animation',

			/**
			 * Selectors
			 */
			formSelectors: inputSelectors,

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
			debug: !!window.wooRede.debug,
		});


		// Workaround to maintain the card data rendered after checkout updates
		Object.values(inputSelectors).reverse().forEach(function (selector) {
			$(selector)[0].dispatchEvent(new CustomEvent('change'));
		})

		$(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('focus'));
		$(inputSelectors.numberInput)[0].dispatchEvent(new CustomEvent('blur'));
	});
});
