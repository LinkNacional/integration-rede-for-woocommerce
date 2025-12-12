<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
final class LknIntegrationRedeForWoocommerceAdmin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lkn-integration-rede-for-woocommerce-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script('lknIntegrationRedeForWoocommerceProFields', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-pro-fields.js', array('jquery'), $this->version, false);

        // Só enfileira o script se a versão PRO estiver desativada
        if (!is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            wp_enqueue_script('lknIntegrationRedeForWoocommerceProInstallments', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-pro-installments.js', array('jquery'), $this->version, false);
        }

        wp_localize_script('lknIntegrationRedeForWoocommerceProFields', 'lknPhpProFieldsVariables', array(
            'proSettings' => 'Configurações PRO',
            'license' => 'Licença',
            'currency' => 'Conversor de Moeda',
            'currencyQuote' => 'Cotação de Moeda',
            'autoCapture' => 'Captura Automática',
            'autoCaptureLabel' => 'Habilita captura automática',
            'customCssShortcode' => 'CSS Personalizado (Shortcode)',
            'customCssBlockEditor' => 'CSS Personalizado (Editor de Blocos)',
            'interestOnInstallments' => 'Juros no parcelamento',
            'interestOnInstallmentsDescTip' => 'Selecione a opção juros ou desconto. Salve para continuar a configuração.',
            'licenseDescTip' => 'Licença para extensões do plugin Rede para WooCommerce.',
            'currencyDescTip' => 'Se habilitado, converte automaticamente o valor do pedido para BRL ao processar o pagamento.',
            'currencyQuoteDescTip' => 'Estas são as taxas de câmbio em tempo real, indicando o valor de cada moeda estrangeira listada em Reais Brasileiros (BRL).',
            'autoCaptureDescTip' => 'Ao habilitar a captura automática, o pagamento é capturado automaticamente imediatamente após a transação.',
            'customCssShortcodeDescTip' => 'Possibilidade de personalizar o CSS do shortcode. Digite o seletor e as regras, exemplo: .checkout{color:green;}.',
            'customCssBlockEditorDescTip' => 'Possibilidade de personalizar o CSS no checkout do editor de blocos. Digite o seletor e as regras, exemplo: .checkout{color:green;}.',
            'becomePRO' => 'PRO',
            'licenseDescription' => 'Licença para extensões do plugin Rede.',
            'currencyDescription' => 'Converte automaticamente valores de pagamento para BRL.',
            'autoCaptureDescription' => 'Captura automaticamente o pagamento uma vez autorizado pela Rede.',
            'autoCaptureDebitLabel' => 'Habilitar captura automática para transações de cartão de crédito',
            'customCssShortcodeDescription' => 'Define regras CSS para o shortcode.',
            'customCssBlockEditorDescription' => 'Define regras CSS para o editor de blocos.',
            'interestOnInstallmentsDescription' => 'Habilita pagamento com juros no parcelamento. Salve para continuar a configuração. Após habilitar os juros do parcelamento, você pode definir o valor dos juros de acordo com o parcelamento.',
            'licenseDataDescription' => 'Salve para habilitar outras opções.',
            'quoteDataDescription' => 'Estas são as taxas de câmbio em tempo real, indicando o valor de cada moeda estrangeira listada em Reais Brasileiros (BRL).',
            'autoCaptureDataDescription' => 'Captura automaticamente o pagamento uma vez autorizado pela Rede.',
            'cssShortcodeDataDescription' => 'Personalize o CSS do Shortcode usando seletores e regras.',
            'cssBlockEditorDataDescription' => 'Personalize o CSS do Editor de Blocos usando seletores e regras.',
            'installmentInterestDataDescription' => 'Aplica uma taxa de juros a cada parcela. Use isso se quiser cobrar extra por parcela.',
        ));

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin.js', array('jquery'), $this->version, false);

        // Localize the script with custom data
        wp_localize_script($this->plugin_name, 'lknPhpVariables', array(
            'plugin_slug' => 'invoice-payment-for-woocommerce',
            'install_nonce' => wp_create_nonce('install-plugin_invoice-payment-for-woocommerce'),
            'invoice_plugin_installed' => is_plugin_active('invoice-payment-for-woocommerce/invoice-payment-for-woocommerce.php'),
            'isProActive' => is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')
        ));

        $gateways = array(
            'maxipago_credit',
            'maxipago_debit',
            'rede_credit',
            'rede_debit',
            'maxipago_pix',
            'rede_pix',
            'integration_rede_pix'
        );

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        $section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '';

        $versions = 'Plugin Rede API v' . INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION . ' | ' . 'PRO v' . 2.1;
        if (defined('REDE_FOR_WOOCOMMERCE_PRO_VERSION')) {
            $versions = 'Plugin Rede API v' . INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION . ' | ' . 'PRO v' . REDE_FOR_WOOCOMMERCE_PRO_VERSION;
        }

        if (isset($_GET['section']) && sanitize_text_field(wp_unslash($_GET['section'])) === 'integration_rede_pix') {
            wp_enqueue_script(
                $this->plugin_name . '-pix-settings',
                plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-pix-settings.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        $allowed_sections = [
            'rede_credit',
            'rede_debit',
            'integration_rede_pix',
            'maxipago_credit',
            'maxipago_debit',
            'maxipago_pix',
            'rede_pix'
        ];

        if (isset($_GET['section']) && in_array(sanitize_text_field(wp_unslash($_GET['section'])), $allowed_sections, true)) {
            wp_enqueue_script(
                $this->plugin_name . '-plugin-rate',
                plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-plugin-rate.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        if ('wc-settings' === $page && 'checkout' === $tab && in_array($section, $gateways, true)) {
            wp_enqueue_script('lknIntegrationRedeForWoocommerceAdminClearLogsButton', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-clear-logs-button.js', array('jquery'), $this->version, false);
            wp_enqueue_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-settings-layout.js', array('jquery'), $this->version, false);
            wp_enqueue_script('lknIntegrationRedeForWoocommerceCard', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-card.js', array('jquery'), $this->version, false);
            wc_get_template(
                'adminCard/adminSettingsCard.php',
                array(
                    'backgrounds' => array(
                        'right' => plugin_dir_url(__FILE__) . 'images/backgroundCardRight.svg',
                        'left' => plugin_dir_url(__FILE__) . 'images/backgroundCardLeft.svg'
                    ),
                    'logo' => plugin_dir_url(__FILE__) . 'images/linkNacionalLogo.webp',
                    'stars' => plugin_dir_url(__FILE__) . 'images/stars.svg',
                    'whatsapp' => plugin_dir_url(__FILE__) . 'images/whatsapp.svg',
                    'telegram' => plugin_dir_url(__FILE__) . 'images/telegram.svg',
                    'versions' => $versions

                ),
                'woocommerce/adminSettingsCard/',
                plugin_dir_path(__FILE__) . '../Includes/templates/'
            );
            wp_localize_script('lknIntegrationRedeForWoocommerceAdminClearLogsButton', 'lknWcRedeTranslations', array(
                'clearLogs' => 'Limpar Logs',
                'alertText' => 'Deseja realmente deletar todos logs dos pedidos?'
            ));
            wp_localize_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', 'lknWcRedeLayoutSettings', array(
                'basic' => plugin_dir_url(__FILE__) . 'images/basicTemplate.png',
                'modern' => plugin_dir_url(__FILE__) . 'images/modernTemplate.png',
            ));
        }
    }
}
