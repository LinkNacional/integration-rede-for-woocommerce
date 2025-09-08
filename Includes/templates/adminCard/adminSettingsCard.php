<?php
if (!defined('ABSPATH')) {
    exit();
}
?>

<div id="lknIntegrationRedeForWoocommerceSettingsCard" style="background-image: url('<?php echo esc_url($backgrounds['right']); ?>'), url('<?php echo esc_url($backgrounds['left']); ?>'); display:none;">
    <div id="lknIntegrationRedeForWoocommerceDivLogo">
        <div>
            <?php //phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage 
            ?>
            <img src=<?php echo esc_url($logo); ?> alt="Logo">
            <?php //phpcs:enable 
            ?>
        </div>
        <p><?php echo esc_attr($versions); ?></p>
    </div>
    <div id="lknIntegrationRedeForWoocommerDivContent">
        <div id="lknIntegrationRedeForWoocommerDivLinks">
            <div>
                <a target="_blank" href=<?php echo esc_url('https://www.linknacional.com.br/wordpress/woocommerce/rede/?utm=plugin'); ?>>
                    <b>•</b><?php echo esc_attr_e('Documentation', 'integration-rede-for-woocommerce'); ?>
                </a>
                <a target="_blank" href=<?php echo esc_url('https://www.linknacional.com.br/wordpress/woocommerce/rede/?utm=plugin'); ?>>
                    <b>•</b><?php echo esc_attr_e('WordPress VIP', 'integration-rede-for-woocommerce'); ?>
                </a>
            </div>
            <div>
                <a target="_blank" href=<?php echo esc_url('https://t.me/wpprobr'); ?>>
                    <b>•</b><?php echo esc_attr_e('Support via Telegram', 'integration-rede-for-woocommerce'); ?>
                </a>
                <a target="_blank" href=<?php echo esc_url('https://cliente.linknacional.com.br/solicitar/wordpress-woo-gratis/?utm=plugin'); ?>>
                    <b>•</b><?php echo esc_attr_e('Free WP Hosting', 'integration-rede-for-woocommerce'); ?>
                </a>
            </div>
        </div>
        <div id="lknIntegrationRedeForWoocommerStarsDiv">
            <a target="_blank" href=<?php echo esc_url('https://wordpress.org/support/plugin/woo-rede/reviews/#new-post'); ?>>
                <p><?php echo esc_attr_e('Rate Plugin', 'integration-rede-for-woocommerce'); ?></p>
                <div>
                    <?php //phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage 
                    ?>
                    <img src=<?php echo esc_url($stars); ?> alt="Logo">
                    <?php //phpcs:enable 
                    ?>
                </div>
            </a>
        </div>
    </div>
</div>