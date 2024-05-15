<?php

use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerce;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceActivator;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceDeactivator;

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LknIntegrationRedeForWoocommerce_VERSION', '1.0.0');

if ( ! defined('INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE')) {
    define('INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE', __DIR__ . '/lkn-integration-rede-for-woocommerce.php');
}

if ( ! defined('INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR')) {
    define('INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR', plugin_dir_path(INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE));
}

if ( ! defined('INTEGRATION_REDE_FOR_WOOCOMMERCE_BASENAME')) {
    define('INTEGRATION_REDE_FOR_WOOCOMMERCE_BASENAME', plugin_basename(INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/LknIntegrationRedeForWoocommerceActivator.php
 */
function activate_LknIntegrationRedeForWoocommerce()
{
	LknIntegrationRedeForWoocommerceActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/LknIntegrationRedeForWoocommerceDeactivator.php
 */
function deactivate_LknIntegrationRedeForWoocommerce()
{
	LknIntegrationRedeForWoocommerceDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_LknIntegrationRedeForWoocommerce');
register_deactivation_hook(__FILE__, 'deactivate_LknIntegrationRedeForWoocommerce');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_LknIntegrationRedeForWoocommerce()
{

	$plugin = new LknIntegrationRedeForWoocommerce();
	$plugin->run();
}
run_LknIntegrationRedeForWoocommerce();
