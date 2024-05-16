<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

/**
 * Fired during plugin activation
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class LknIntegrationRedeForWoocommerceActivator {
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate(): void {
        if ( ! wp_next_scheduled( 'update_rede_orders' ) ) {
            wp_schedule_event( time(), 'hourly', 'update_rede_orders' );
        }
    }
}
