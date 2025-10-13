=== Integration Rede for WooCommerce ===
Contributors: linknacional,MarcosAlexandre
Donate link: https://www.linknacional.com/wordpress/plugins/
Tags: woocommerce,payment,card,credit
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 4.1.0
Requires PHP: 7.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Receive payments through credit and debit cards, from different brands, using 3DS authentication technology.

== Description ==

Integrate Rede or Maxipago into your WooCommerce store and enable your customers to pay by credit or debit card

A [Rede](https://www.userede.com.br/) is part of the Itaú Unibanco group and is a acquiring company, being responsible for the capture, transmission and financial settlement of Visa, Mastercard, Elo, American Express, Hipercard, Hyper, Diners Club International, Cabal credit card transactions. Discover, China Union Pay, Aura, Sorocred, Coopercred, Sicredi, More !, Calcard, Banescard, Avista! in the Brazilian territory.

O [Maxipago](https://www.userede.com.br/n/gateway-de-pagamento-rede) is part of the Rede Group, provides a secure and efficient payment gateway platform for businesses to accept major credit and debit cards in Brazil. With advanced security and fraud prevention, it ensures transaction safety and customer trust, offering seamless integration and support for businesses of all sizes.

**Dependencies**

Integration [Rede for WooCommerce plugin](https://www.linknacional.com.br/wordpress/woocommerce/rede/) is dependent on WooCommerce plugin, please make sure WooCommerce is installed and properly configured before starting Integration Rede for WooCommerce installation.

*PHP Dependencies*
* This plugins uses the [Rede SDK](https://github.com/DevelopersRede/erede-php) to process payemnts with Rede API.

**User instructions**

1. Search the WordPress sidebar for 'Integration Rede for WooCommerce'.

2. In the WooCommerce options, navigate to 'Payments' and then proceed to the settings for 'Rede' or 'Maxipago' as your preferred choice.

3. Configure the required credentials for your selected payment gateway, such as PV and Token for Rede or Merchant ID and Merchant Key for Maxipago.

4. Save your settings.

You have successfully configured Integration Rede for WooCommerce and enabled your customers to pay by credit or debit card.

== Installation ==

1. Look in the sidebar for the WordPress plugins area;

2. In installed plugins look for the 'add new' option in the header;

3. Click on the 'submit plugin' option in the page title and upload the integration-rede-for-woocommerce-master.zip plugin;

4. Click on the 'install now' button and then activate the installed plugin;

The Integration Rede for WooCommerce plugin is now live and working.

== Usage ==

= Payments Settings =

1. After installing the plugin, access the WordPress admin dashboard and navigate to the WooCommerce settings.
2. In the sidebar menu, click on "WooCommerce" and then on "Settings".
3. In the "Payments" tab, you'll see a list of available payment methods.
4. Locate "Rede" or "Maxipago" in the list of payment methods and go to settings.
5. Enter the required configuration information for each payment method, such as PV and Token for Rede or or Merchant ID and Merchant Key for Maxipago.
6. After configuring the payment methods, make sure to activate each one by toggling the switch.

== Frequently Asked Questions ==

= What is the license of the plugin? =

* This plugin is released under a GPL license.

= What do I need to use this plugin? =

* Have installed the WooCommerce plugin.

== Changelog ==
# 4.1.0 - 2025/10/08
* New style for gateway settings.
* More detailed descriptions in the settings.

# 4.0.9 - 2025/10/09
* Fix in icons display.

# 4.0.8 - 2025/09/22
* Fix in tax calculation for installments.

# 4.0.7 - 2025/09/19
* Fix in version release.

# 4.0.6 - 2025/09/15
* Fix in the PIX route.

# 4.0.5 - 2025/09/15
* Fix in the PIX route.

# 4.0.4 - 2025/09/15
* Fix installment values update
* Fixed payment refunds.
* Fixed the endpoint for the PIX verification route.

# 4.0.3 - 2025/09/12
* Fix refund function.

# 4.0.2 - 2025/09/05
* Update to the sales links.
* Fix in the plugin's textdomain.
* Plugin icon in the gateway settings

# 4.0.1 - 2025/09/03
* Legacy mode installment calculation fix

# 4.0.0 - 2025/08/25
* Addition of the currency conversion field.
* Bug and warning fixes.

# 3.11.0 - 2025/07/23
* Add PRO configuration to display the "Finalize and Generate PIX" button at checkout.
* Add links in settings to guide users to documentation and tutorials.
* Fix text display issue in plugin review.

# 3.10.1 - 2025/05/07
* Fix description of payment methods.

# 3.10.0 - 2025/07/08
* Add compatibility with PRO interest or discount information in the checkout.

# 3.9.3 - 2025/06/13
* Changed the feedback for logs in the Rede payment method.

# 3.9.2 - 2025/06/06
* Fix for the PIX verification URL.

# 3.9.1 - 2025/06/02
* Fix installment limit per product.
* Fix log display in the order.

# 3.9.0 - 2025/05/15
* Add compatibility with PRO configuration to add discounts on installments.
* Add compatibility with PRO configuration to set the minimum value for interest-free installments.

# 3.8.0 - 2025/05/08
- Add setting to display logs within orders.
- Fix logic for the minimum installment value.

# 3.7.5 - 2025/05/07
* Fix debit payment issue with Maxipago.

# 3.7.4 - 2025/05/06
* Fix Correction in payment refund
* Fixed issue in plugin release action

# 3.7.3 - 2025/05/06
* Fix Correction in payment refund

# 3.7.2 - 2025/04/08
* Update "Tested up to" to the latest WordPress version.

# 3.7.1 - 2025/03/18
* Fix in debit request with Maxipago.

# 3.7.0 - 2025/03/11
* Add 3DS validation for debit payments with Maxipago;
* Add endpoint to receive notifications for debit method with Maxipago.

# 3.6.4 - 2025/02/27
* Add conversion of the total purchase amount;
- Fixed MaxiPago card fields;
- Fixed MaxiPAgo API return code detection.

# 3.6.3 - 2025/02/24
* Fix settings when the PRO plugin is deactivated.

# 3.6.2 - 2025/02/03
* Fix CVV field validation.

# 3.6.1 - 2025/01/24
* Settings fixed.

# 3.6.0 - 2025/01/24
* New payment method via Pix (Rede).
* Added the download notice for the plugin: fraud-detection-for-woocommerce.
* Added the plugin rating message in the footer.

= 3.5.3 = *2025/01/14*
* Fix order total when delivery fees are applied.

= 3.5.2 = *2024/12/18*
* Fix fatal error in translation function call.

= 3.5.1 = *2024/12/13*
* Security correction in the card CVV (debit and credit).

= 3.5.0 = *2024/12/05*
* Update in SDK version;
* Add animated card in WooCommerce block editor checkout.

= 3.4.3 = *2024/11/11*
* Correction of undefined attribute warnings;
* Correction of compatibility statement for WooCommerce block editor;
* Improved treatment of payment variables.

= 3.4.2 = *2024/11/08*
* Change layout of admin settings.

= 3.4.1 = *2024/10/24*
* Change in notification script logic.

= 3.4.0 = *2024/10/18*
* Fix Compatibility issues with WooCommerce block editor;
* Change Updated free hosting texts;
* Change layout of admin settings;
* Add Configuration to disable the soft descriptor field;
* Add Card brand information added to order details.

= 3.3.2 = *2024/10/01*
* Fix CPF field in Brazilian fields plugin.

= 3.3.1 = *2024/09/25*
* Fix installment display.

= 3.3.0 = *2024/09/03*
* Add compatibility with new PRO plugin settings;
* Added configuration to change status of paid orders.

= 3.2.0 = *2024/08/06*
* Addition of Rede PRO settings as read-only;
* Add setting to load the infinite loading fix script.

= 3.1.3 = *2024/07/30*
* Improvement in script loading;
* Fixed field validation generating new orders.

= 3.1.2 = *2024/07/23*
* Correction of unrecognized translations;
* Correction of invalid order generation;
* Adjustments to field validation;
* Code optimization.

= 3.1.1 = *2024/07/22*
* Fixed scripts for card animation.

= 3.1.0 = *2024/07/16*
* Added compatibility with the PRO plugin.
* Added compatibility option to load legacy CSS.
* Added easier button to view transaction logs;
* Issuer bug fix;
* Refund bug fix;
* Correction of transaction bug not being captured automatically;
* Fixed field rendering bug on the checkout page.

= 3.0.5 = *2024/05/29*
* Fix refund function.

= 3.0.4 = *2024/05/24*
* Fix card animation loop.

= 3.0.3 = *2024/05/23*
* Fix errors in function to get the total purchase amount.

= 3.0.2 = *2024/05/22*
* Fix payment method layout errors at checkout.

= 3.0.1 = *2024/05/21*
* Fix payment method errors at checkout.

= 3.0.0 = *2024/05/08*
* Complete refactoring of the plugin to object-oriented architecture;
* Addition of debit option for payments with Rede;
* Addition of Maxipago for payment methods.

= 2.1.0 = *2020/12/05*
* Update compatibility information;
* Correction of the order id;
* Implementation of installment filter;
* Implementation of a filter to display the Network data on the order page only if this is the payment method used;
* Improvements to the card layout on the checkout page.

= 2.0.2 = *2020/05/23*
* Update compatibility information;
* Correction of the order id.

= 2.0.1 = *2020/05/04*
* Update compatibility information;
* Correction of the internationalization of error messages on the checkout page.

= 2.0.0 = *2019/11/02*
* Correction of the number of installments display;
* Year placeholder adjustment at card expiration;
* Plugin internationalization and translation for pt_BR;
* Inclusion of credit card banners icons;
* Expiration date now accepts 2 or 4 digits for year as well;
* Sanitize inputs fields.

== Screenshots ==

1. Payment methods list.
2. Rede Credit settings page.
3. Rede Credit front inputs page.
4. Maxipago Credit front inputs page.
5. Maxipago Credit summary page.
6. Rede Credit front page legacy with installments.
7. Rede Debit settings page.
8. Rede and Maxipago payment list.

== Upgrade Notice ==
= 3.5.3 =
* Fix order total when delivery fees are applied.

= 3.5.2 =
* Fix fatal error in translation function call

= 3.5.0 =
* Update in SDK version;
* Add animated card in WooCommerce block editor checkout.

= 3.4.2 =
* Change layout of admin settings.

= 3.4.1 =
* Change in notification script logic.

= 3.4.0 =
* Fix Compatibility issues with WooCommerce block editor;
* Change Updated free hosting texts;
* Change layout of admin settings;
* Add Configuration to disable the soft descriptor field;
* Add Card brand information added to order details.

= 3.3.2 =
* Fix CPF field in Brazilian fields plugin.

= 3.3.1 =
* Fix installment display.

= 3.3.0 =
* Add compatibility with new PRO plugin settings.

= 3.2.0 =
* Add Rede PRO settings as read-only;
* Add setting to load the infinite loading fix script.

= 3.1.2 =
* Correction of unrecognized translations;
* Correction of invalid order generation;
* Adjustments to field validation;
* Code optimization.

= 3.1.1 =
* Fixed scripts for card animation.

= 3.1.0 =
* Addition compatibility with the PRO plugin.

= 3.0.5 =
* Fix refund function.

= 3.0.4 =
* Fix card animation loop.

= 3.0.3 =
* Fix errors in function to get the total purchase amount.

= 3.0.2 =
* Fix payment method layout errors at checkout.

= 3.0.1 =
* Fix payment method errors at checkout.

= 3.0.0 =
* Updating your plugin may cause your payment method to lose some settings;
* This update adds compatibility with block based checkout.

= 1.0.0 =
* Plugin launch.
