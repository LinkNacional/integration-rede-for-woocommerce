# Integration Rede for WooCommerce

Contributors: MarcosAlexandre  
Tags: rede, e-rede, redecard, woocommerce  
Requires at least: 5.0  
Tested up to: 5.5  
WC requires at least: 3.0.0  
WC tested up to: 4.3.2  
Stable tag: 2.1.0  
Requires PHP: 7.0  
License: MIT License URI: https://opensource.org/licenses/MIT  
Rede API integration for WooCommerce  

## Description

Use Rede API integration for WooCommerce in your WooCommerce store and allow your customers to pay by credit card.

A [Rede](https://www.userede.com.br/) is part of the Itaú Unibanco group and is a acquiring company, being responsible for the capture, transmission and financial settlement of Visa, Mastercard, Elo, American Express, Hipercard, Hyper, Diners Club International, Cabal credit card transactions. Discover, China Union Pay, Aura, Sorocred, Coopercred, Sicredi, More !, Calcard, Banescard, Avista! in the Brazilian territory.

## Development

This version of the **Integration Rede for WooCommerce** plugin was developed without any encouragement from Rede. This means that none of the developers of this plugin have any bonds with the Rede and we count on your help to improve the code and operation of this plugin.

## Compatibility

Compatible since version 3.0.x of WooCommerce.

Works with the plugin: \* [WooCommerce](https://wordpress.org/plugins/woocommerce/)

## Installation

Download the plugin Upload the plugin to the wp-content/plugins directory, Go to “plugins” in your WordPress admin, then click activate.

## Requirements:

-   Have a website ready with WordPress and WooCommerce installed.
-   Use SSL certificate (2048 bit recommended).
-   Have registration on [Rede](https://www.userede.com.br/new/e-rede#telefone)

## Plugin Settings:

You can access the plugin settings screen from the WordPress admin page under `WooCommerce -> Settings -> Payments -> Pay with the Rede`.

The plugin works with the **Test** and **Production** environments, where you must use the **Test** environment to test the integration before using it in the **Production** environment. Once it is tested and validated, you can use the **Production** environment where you can sign in with the **PV** and the **Token** of ecommerce affiliation with the Rede.

### Notes on the test environment

In the **Test environment** you can use some test cards available in the [Rede Integration Guide](https://www.userede.com.br/desenvolvedores/pt/produto/e-Rede#tutorial) by accessing the `Playground Tutorial -> How to use the playground`, just do the [Register](https://www.userede.com.br/desenvolvedores/pt/cadastro).

## Doubts?

You can ask questions by:

-   Using our [forum on Github](https://github.com/marcos-alexandre82/integration-rede-for-woocommerce/issues).
-   Creating a topic in the [WordPress help forum](https://wordpress.org/support/plugin/integration-rede-for-woocommerce).

## Credits

This plugin is a fork that fixes some bugs and implements new features to the plugin developed in:

-   [DevelopersRede](https://github.com/DevelopersRede/woocommerce).

### Credcards icons have been forkled from Storefront in:

-   [Storefront](https://github.com/woocommerce/storefront/tree/master/assets/images/credit-cards).

### Banner:

-   [Freepik](https://br.freepik.com/vetores-gratis/conjunto-de-banner-de-pagamento_4378405.htm#page=3&query=cartao+de+credito+banner&position=33).

### Icon:

-   [Freepik](https://br.freepik.com/vetores-gratis/icones-economia_794700.htm#page=1&query=cartao%20de%20credito&position=20).

## Contributors

You can contribute source code on our page at [GitHub](https://github.com/marcos-alexandre82/integration-rede-for-woocommerce/issues).

## Frequently Asked Questions

### What is the license of the plugin?

-   This plugin is licensed as MIT.

### What do I need to use this plugin?

-   Have installed the WooCommerce plugin.

## Changelog

### 2.1.0 - 2020/07/25

-   Update compatibility information
-   Correction of the order id
-   Implementation of installment filter
-   Implementation of a filter to display the Network data on the order page only if this is the payment method used.

### 2.0.2 - 2020/05/23

-   Update compatibility information
-   Correction of the order id

### 2.0.1 - 2020/05/04

-   Update compatibility information
-   Correction of the internationalization of error messages on the checkout page

### 2.0.0 - 2019/11/02

-   Correction of the number of installments display
-   Year placeholder adjustment at card expiration
-   Plugin internationalization and translation for pt\_BR
-   Inclusion of credit card banners icons
-   Expiration date now accepts 2 or 4 digits for year as well
-   Sanitize inputs fields

## Upgrade Notice

Corrected the display of the installments, changed the card validity field to accept 2 or 4 digits, made the internationalization and translation process for pt\_BR, and implemented WordPress Coding Standards. Also added the icons of the card flags.
