<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$plugin_slug = 'woocommerce';

if ( current_user_can( 'install_plugins' ) ) {
	$url = wp_nonce_url(
		self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ),
		'install-plugin_' . $plugin_slug
	);
} else {
	$url = 'http://wordpress.org/plugins/' . $plugin_slug;
}
?>

<div class="error">
	<p>
		<strong>
		<?php
		esc_attr_e(
			'Integration Rede for WooCommerce Disabled',
			'integration-rede-for-woocommerce'
		);
		?>
				</strong>: 
				<?php
				printf(
					__(
						'This plugin depends on the last version of %s to work!',
						'integration-rede-for-woocommerce'
					),
					'<a href="' . esc_url( $url ) . '">' . esc_attr__( 'WooCommerce', 'integration-rede-for-woocommerce' ) . '</a>'
				);
				?>
			</p>
</div>
