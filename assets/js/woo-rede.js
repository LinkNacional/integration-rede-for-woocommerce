jQuery(document).ready( function(){
	jQuery( 'body' )
	.on( 'init init_checkout updated_checkout payment_method_selected checkout_error update_checkout updated_wc_div ', function() {
			
		var card = new Card({
			form: '.wc-payment-rede-form-fields',
			container: '.card-wrapper',

			/**
			 * Selectors
			 */
			formSelectors: {
				numberInput: 'input[name="rede_credit_number"]',
				expiryInput: 'input[name="rede_credit_expiry"]',
				cvcInput: 'input[name="rede_credit_cvc"]',
				nameInput: 'input[name="rede_credit_holder_name"]'
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
			 * Translation Portuguese Brasilian
			 */
			messages: {
				validDate: 'VÁLIDO\nATÉ',
				monthYear: ''
			},
		});

	});
});
