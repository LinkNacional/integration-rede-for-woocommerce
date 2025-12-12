<?php
if (! defined('ABSPATH')) {
    exit();
}

$integration_rede_for_woocommerce_plugin_slug = 'woocommerce';

if (current_user_can('install_plugins')) {
    $integration_rede_for_woocommerce_url = wp_nonce_url(
        self_admin_url('update.php?action=install-plugin&plugin=' . $integration_rede_for_woocommerce_plugin_slug),
        'install-plugin_' . $integration_rede_for_woocommerce_plugin_slug
    );
} else {
    $integration_rede_for_woocommerce_url = 'http://wordpress.org/plugins/' . $integration_rede_for_woocommerce_plugin_slug;
}
?>

<div class="error">
    <p>
        <strong>
            <?php
            esc_attr_e(
                'Integration Rede Itaú for WooCommerce — Payment PIX, Credit Card and Debit Disabled',
                'integration-rede-for-woocommerce'
            );
            ?>
        </strong>:
        <?php
        printf(
            // translators: %s is the name of the plugin required for this one to work.
            esc_attr__(
                'This plugin depends on the last version of %s to work!',
                'integration-rede-for-woocommerce'
            ),
            '<a href="' . esc_url($integration_rede_for_woocommerce_url) . '">' . esc_attr__('WooCommerce', 'woo-rede') . '</a>'
        );
        ?>
    </p>
</div>