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
			let wcForm = document.getElementById('mainform')
			//Pega sempre o ultimo form-table para mostraro link

			const noticeDiv = document.createElement('div')
			noticeDiv.setAttribute('style', 'background-color: #fcf9e8;color: #646970;border: solid 1px #d3d3d3;border-left: 4px #dba617 solid;font-size: 16px;margin-top: 10px;')

			noticeDiv.innerHTML = '<a  href="https://cliente.linknacional.com.br/solicitar/wordpress-woo-gratis/" target="_blank" style="text-decoration:none; display: block;padding: 10px;">Parabéns! Você ganhou uma hospedagem WooCommerce grátis por 12 meses. Solicite agora!</a>'

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
