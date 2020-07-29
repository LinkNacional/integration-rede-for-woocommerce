/*
document.addEventListener('DOMContentLoaded', function () {
	document.body.addEventListener('init_checkout updated_checkout checkout_error', function (e) {
*/
jQuery( function( $ ) {
	jQuery(document.body).on('init_checkout updated_checkout checkout_error', function(){

		var card = new Card({
			form: '.woocommerce-checkout',
			container: '.card-animation',

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
