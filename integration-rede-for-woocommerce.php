<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://linknacional.com.br
 * @since             1.0.0
 * @package           LknIntegrationRedeForWoocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Integration Rede for WooCommerce
 * Description:       Receba pagamentos por meio de cartões de crédito e débito, de diferentes bandeiras, usando a tecnologia de autenticação 3DS e recursos avançados de proteção contra fraudes.
 * Version:           3.4.1
 * Author:            Link Nacional
 * Author URI:        https://linknacional.com.br/wordpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-rede
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

require_once 'lkn-integration-rede-for-woocommerce-file.php';