# Rede for WooCommerce PIX, Credit card and Debit card

Contributors: linknacional, MarcosAlexandre  
Donate link: [Support Us](https://www.linknacional.com/wordpress/plugins/)  
Tags: woocommerce, payment, credit card, debit card, PIX  
Requires at least: 5.0  
Tested up to: 6.8  
Stable tag: 4.1.0  
Requires PHP: 7.2  
License: GPLv3 or later  
License URI: [GPL License](http://www.gnu.org/licenses/gpl-3.0.txt)  

---

## Transform Your WooCommerce Store with Seamless Payments

**Integration Rede for WooCommerce** is the ultimate solution for enabling secure and efficient payment options in your online store. With support for **credit cards**, **debit cards**, and **PIX payments**, this plugin empowers your business to offer a seamless checkout experience. Whether you want to enable **installment payments**, **tokenized card storage** for returning customers, or **3DS authentication**, this plugin has you covered.

### Why Choose Integration Rede for WooCommerce?

- **Multiple Payment Options**: Accept payments via credit cards, debit cards, and PIX.
- **Installments**: Offer flexible installment plans to your customers.
- **Secure Transactions**: Includes 3DS authentication and advanced fraud prevention.
- **Tokenization**: Save card details securely for returning customers.
- **Easy Integration**: Works seamlessly with WooCommerce and WordPress.
- **Customizable**: Configure payment settings to match your business needs.
- **Sandbox Mode**: Test transactions in a secure environment before going live.

---

## Features at a Glance

- **Credit and Debit Card Payments**: Accept payments from major card brands like Visa, Mastercard, Elo, and more.
- **PIX Payments**: Enable instant bank transfers with PIX.
- **Installment Plans**: Allow customers to split payments into manageable installments.
- **3DS Authentication**: Ensure secure transactions with strong customer authentication.
- **Webhooks**: Stay updated with real-time payment notifications.
- **Refunds and Captures**: Manage refunds and payment captures directly from your WooCommerce dashboard.
- **Detailed Logs**: Debug and troubleshoot with comprehensive logging.

---

## Installation

### 1. Using the WordPress Admin Dashboard (Recommended)
1. Navigate to **Plugins** → **Add New**.
2. Click **Upload Plugin** and select the `integration-rede-for-woocommerce-master.zip` file.
3. Click **Install Now** and then **Activate**.
4. Go to **WooCommerce** → **Settings** → **Payments** to configure the plugin.

### 2. Manual Installation via FTP
1. Extract the plugin ZIP file.
2. Upload the extracted folder to `wp-content/plugins/`.
3. Activate the plugin in the WordPress admin dashboard under **Plugins**.

### 3. WP-CLI Installation
1. Upload the plugin folder to `wp-content/plugins/`.
2. Run the command:  
   ```bash
   wp plugin activate integration-rede-for-woocommerce
   ```

---

## Frequently Asked Questions

**What is the license of the plugin?**  
This plugin is released under a GPL license.

**What do I need to use this plugin?**  
You need to have the WooCommerce plugin installed.

---

## Changelog

### 4.1.0 - 2025/10/08
- New style for gateway settings.
- More detailed descriptions in the settings.

### 4.0.9 - 2025/10/09
- Fix in icons display.

### 4.0.8 - 2025/09/22
- Fix in tax calculation for installments.

### 4.0.7 - 2025/09/19
- Fix in version release.

### 4.0.6 - 2025/09/15
- Fix in the PIX route.

### 4.0.5 - 2025/09/15
- Fix in the PIX route.

### 4.0.4 - 2025/09/15
- Fix installment values update
- Fixed payment refunds.
- Fixed the endpoint for the PIX verification route.

### 4.0.3 - 2025/09/12
- Fix refund function.

### 4.0.2 - 2025/09/05
- Update to the sales links.
- Fix in the plugin's textdomain.
- Plugin icon in the gateway settings

### 4.0.1 - 2025/09/03
- Legacy mode installment calculation fix

### 4.0.0 - 2025/08/25
- Addition of the currency conversion field.
- Bug and warning fixes.

### 3.11.0 - 2025/07/23
- Add PRO configuration to display the "Finalize and Generate PIX" button at checkout.
- Add links in settings to guide users to documentation and tutorials.
- Fix text display issue in plugin review.

### 3.10.1 - 2025/05/07
- Fix description of payment methods.

### 3.10.0 - 2025/07/08
- Add compatibility with PRO interest or discount information in the checkout.

### 3.9.3 - 2025/06/13
- Changed the feedback for logs in the Rede payment method.

### 3.9.2 - 2025/06/06
- Fix for the PIX verification URL.

### 3.9.1 - 2025/06/02
- Fix installment limit per product.
- Fix log display in the order.

### 3.9.0 - 2025/05/15
- Add compatibility with PRO configuration to add discounts on installments.
- Add compatibility with PRO configuration to set the minimum value for interest-free installments.

### 3.8.0 - 2025/05/08
- Add setting to display logs within orders.
- Fix logic for the minimum installment value.

### 3.7.5 - 2025/05/07
- Fix debit payment issue with Maxipago.

### 3.7.4 - 2025/05/06
- Fix Correction in payment refund
- Fixed issue in plugin release action

### 3.7.3 - 2025/05/06
- Fix Correction in payment refund

### 3.7.2 - 2025/04/08
- Update "Tested up to" to the latest WordPress version.

### 3.7.1 - 2025/03/18
- Fix in debit request with Maxipago.

### 3.7.0 - 2025/03/11
- Add 3DS validation for debit payments with Maxipago;
- Add endpoint to receive notifications for debit method with Maxipago.

### 3.6.4 - 2025/02/27
- Add conversion of the total purchase amount;
- Fixed MaxiPago card fields;
- Fixed MaxiPAgo API return code detection.

### 3.6.3 - 2025/02/24
- Fix settings when the PRO plugin is deactivated.

### 3.6.2 - 2025/02/03
- Fix CVV field validation.

### 3.6.1 - 2025/01/24
- Settings fixed.

### 3.6.0 - 2025/01/24
- New payment method via Pix (Rede).
- Added the download notice for the plugin: fraud-detection-for-woocommerce.
- Added the plugin rating message in the footer.

### 3.5.3 - 2025/01/14
- Fix order total when delivery fees are applied.

### 3.5.2 - 2024/12/18
- Fix fatal error in translation function call.

### 3.5.1 - 2024/12/13
- Security correction in the card CVV (debit and credit).

### 3.5.0 - 2024/12/05
- Update in SDK version;
- Add animated card in WooCommerce block editor checkout.

### 3.4.3 - 2024/11/11
- Correction of undefined attribute warnings;
- Correction of compatibility statement for WooCommerce block editor;
- Improved treatment of payment variables.

### 3.4.2 - 2024/11/08
- Change layout of admin settings.

### 3.4.1 - 2024/10/24
- Change in notification script logic.

### 3.4.0 - 2024/10/18
- Fix Compatibility issues with WooCommerce block editor;
- Change Updated free hosting texts;
- Change layout of admin settings;
- Add Configuration to disable the soft descriptor field;
- Add Card brand information added to order details.

### 3.3.2 - 2024/10/01
- Fix CPF field in Brazilian fields plugin.

### 3.3.1 - 2024/09/25
- Fix installment display.

### 3.3.0 - 2024/09/03
- Add compatibility with new PRO plugin settings;
- Added configuration to change status of paid orders.

### 3.2.0 - 2024/08/06
- Addition of Rede PRO settings as read-only;
- Add setting to load the infinite loading fix script.

### 3.1.3 - 2024/07/30
- Improvement in script loading;
- Fixed field validation generating new orders.

### 3.1.2 - 2024/07/23
- Correction of unrecognized translations;
- Correction of invalid order generation;
- Adjustments to field validation;
- Code optimization.

### 3.1.1 - 2024/07/22
- Fixed scripts for card animation.

### 3.1.0 - 2024/07/16
- Added compatibility with the PRO plugin.
- Added compatibility option to load legacy CSS.
- Added easier button to view transaction logs;
- Issuer bug fix;
- Refund bug fix;
- Correction of transaction bug not being captured automatically;
- Fixed field rendering bug on the checkout page.

### 3.0.5 - 2024/05/29
- Fix refund function.

### 3.0.4 - 2024/05/24
- Fix card animation loop.

### 3.0.3 - 2024/05/23
- Fix errors in function to get the total purchase amount.

### 3.0.2 - 2024/05/22
- Fix payment method layout errors at checkout.

### 3.0.1 - 2024/05/21
- Fix payment method errors at checkout.

### 3.0.0 - 2024/05/08
- Complete refactoring of the plugin to object-oriented architecture;
- Addition of debit option for payments with Rede;
- Addition of Maxipago for payment methods.

### 2.1.0 - 2020/12/05
- Update compatibility information;
- Correction of the order id;
- Implementation of installment filter;
- Implementation of a filter to display the Network data on the order page only if this is the payment method used;
- Improvements to the card layout on the checkout page.

### 2.0.2 - 2020/05/23
- Update compatibility information;
- Correction of the order id.

### 2.0.1 - 2020/05/04
- Update compatibility information;
- Correction of the internationalization of error messages on the checkout page.

### 2.0.0 - 2019/11/02
- Correction of the number of installments display;
- Year placeholder adjustment at card expiration;
- Plugin internationalization and translation for pt_BR;
- Inclusion of credit card banners icons;
- Expiration date now accepts 2 or 4 digits for year as well;
- Sanitize inputs fields.

---

## Screenshots

1. Payment methods list.
2. Rede Credit settings page.
3. Rede Credit front inputs page.
4. Maxipago Credit front inputs page.
5. Maxipago Credit summary page.
6. Rede Credit front page legacy with installments.
7. Rede Debit settings page.
8. Rede and Maxipago payment list.

---

## Upgrade Notice

### 3.5.3
- Fix order total when delivery fees are applied.

### 3.5.2
- Fix fatal error in translation function call

### 3.5.0
- Update in SDK version;
- Add animated card in WooCommerce block editor checkout.

### 3.4.2
- Change layout of admin settings.

### 3.4.1
- Change in notification script logic.

### 3.4.0
- Fix Compatibility issues with WooCommerce block editor;
- Change Updated free hosting texts;
- Change layout of admin settings;
- Add Configuration to disable the soft descriptor field;
- Add Card brand information added to order details.

### 3.3.2
- Fix CPF field in Brazilian fields plugin.

### 3.3.1
- Fix installment display.

### 3.3.0
- Add compatibility with new PRO plugin settings.

### 3.2.0
- Add Rede PRO settings as read-only;
- Add setting to load the infinite loading fix script.

### 3.1.2
- Correction of unrecognized translations;
- Correction of invalid order generation;
- Adjustments to field validation;
- Code optimization.

### 3.1.1
- Fixed scripts for card animation.

### 3.1.0
- Addition compatibility with the PRO plugin.

### 3.0.5
- Fix refund function.

### 3.0.4
- Fix card animation loop.

### 3.0.3
- Fix errors in function to get the total purchase amount.

### 3.0.2
- Fix payment method layout errors at checkout.

### 3.0.1
- Fix payment method errors at checkout.

### 3.0.0
- Updating your plugin may cause your payment method to lose some settings;
- This update adds compatibility with block based checkout.

### 1.0.0
- Plugin launch.
