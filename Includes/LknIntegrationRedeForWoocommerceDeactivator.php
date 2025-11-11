<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

/**
 * Fired during plugin deactivation
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class LknIntegrationRedeForWoocommerceDeactivator {
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate(): void {
        wp_clear_scheduled_hook( 'update_rede_orders' );
        wp_clear_scheduled_hook( 'lkn_rede_refresh_oauth_token' );
        
        // Limpar cache de tokens OAuth2 para todos os gateways
        $gateways = array('rede_credit', 'rede_debit', 'integration_rede_pix', 'rede_pix');
        $environments = array('test', 'production');
        
        foreach ($gateways as $gateway_id) {
            foreach ($environments as $environment) {
                delete_option('lkn_rede_oauth_token_' . $gateway_id . '_' . $environment);
            }
        }
        
        // Limpar cache de tokens legados
        delete_option('lkn_rede_oauth_token_test');
        delete_option('lkn_rede_oauth_token_production');
    }
}
