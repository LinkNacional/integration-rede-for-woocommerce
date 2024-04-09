window.jQuery(function ($) {
	setTimeout(() => {
		
	}, 100);
	const $form = $('.woocommerce .woocommerce-checkout');
	const inputSelectors = {
		numberInput: '#rede-card-number',
		nameInput: '#rede-card-holder-name',
		expiryInput: '#rede-card-expiry',
		cvcInput: '#rede-card-cvc',
	};
	
	
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

	/* setTimeout(() => {
		let blockUIs = document.querySelectorAll('.blockUI');
		// Convertendo o NodeList em um array usando o método Array.from()
		Array.from(blockUIs).map((blockUI)=>{
			blockUI.style.display = 'none';
		});
		
	}, 300); */ //TODO Encontrar hook para executar a remoção do load mesmo que o metodo esteja desativado caso não resolva na API
});
