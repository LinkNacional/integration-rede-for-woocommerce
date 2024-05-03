(function ($) {
	$(window).load(function () {

		const adminPage = lknFindGetParameter('section')
		const pluginPages = [
			'maxipago_credit',
			'maxipago_debit',
			'rede_credit',
			'rede_debit',
		]
		if (adminPage && pluginPages.includes(adminPage)) {
			let wcForm = document.getElementsByClassName('form-table')
			//Pega sempre o ultimo form-table para mostraro link
			wcForm = wcForm[wcForm.length - 1]
			const noticeDiv = document.createElement('div')
			noticeDiv.setAttribute('style', 'padding: 10px 5px;background-color: #fcf9e8;color: #646970;border: solid 1px lightgrey;border-left-color: #dba617;border-left-width: 4px;font-size: 14px;min-width: 625px;margin-top: 10px;')

			noticeDiv.innerHTML = '| <a href="https://www.linknacional.com.br/wordpress/woocommerce/cielo/" target="_blank">Hospedagem WooCommerce grátis por 12 meses</a>'

			wcForm.append(noticeDiv)
		}

		function lknFindGetParameter(parameterName) {
			let result = null
			let tmp = []
			location.search
				.substr(1)
				.split('&')
				.forEach(function (item) {
					tmp = item.split('=')
					if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1])
				})
			return result
		}
	});
})(jQuery);
