# Rede for WooCommerce PIX, Credit card and Debit card

Contributors: linknacional, MarcosAlexandre  
Donate link: https://www.linknacional.com/wordpress/plugins/  
Tags: woocommerce, payment, credit card, debit card, PIX  
Requires at least: 5.0  
Tested up to: 6.8  
Stable tag: 4.1.0  
Requires PHP: 7.2  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

---

## Transform Your WooCommerce Store with Seamless Payments

Integration Rede for WooCommerce is the ultimate solution for enabling secure and efficient payment options in your online store. With support for credit cards, debit cards, and PIX payments, this plugin empowers your business to offer a seamless checkout experience. Whether you want to enable installment payments, tokenized card storage for returning customers, or 3DS authentication, this plugin has you covered.

### Why Choose Integration Rede for WooCommerce?

- Multiple Payment Options: Accept payments via credit cards, debit cards, and PIX.
- Installments: Offer flexible installment plans to your customers.
- Secure Transactions: Includes 3DS authentication and advanced fraud prevention.
- Tokenization: Save card details securely for returning customers.
- Easy Integration: Works seamlessly with WooCommerce and WordPress.
- Customizable: Configure payment settings to match your business needs.
- Sandbox Mode: Test transactions in a secure environment before going live.

---

## Features at a Glance

- Credit and Debit Card Payments: Accept payments from major card brands like Visa, Mastercard, Elo, and more.
- PIX Payments: Enable instant bank transfers with PIX.
- Installment Plans: Allow customers to split payments into manageable installments.
- 3DS Authentication: Ensure secure transactions with strong customer authentication.
- Webhooks: Stay updated with real-time payment notifications.
- Refunds and Captures: Manage refunds and payment captures directly from your WooCommerce dashboard.
- Detailed Logs: Debug and troubleshoot with comprehensive logging.

---

## Installation

### 1. Using the WordPress Admin Dashboard (Recommended)
1. Navigate to Plugins → Add New.
2. Click Upload Plugin and select the `integration-rede-for-woocommerce-master.zip` file.
3. Click Install Now and then Activate.
4. Go to WooCommerce → Settings → Payments to configure the plugin.

### 2. Manual Installation via FTP
1. Extract the plugin ZIP file.
2. Upload the extracted folder to `wp-content/plugins/`.
3. Activate the plugin in the WordPress admin dashboard under Plugins.

### 3. WP-CLI Installation
1. Upload the plugin folder to `wp-content/plugins/`.
2. Run:
    ```bash
    wp plugin activate integration-rede-for-woocommerce
    ```

---

## Configuration

Go to WooCommerce → Settings → Payments.  
Select Rede or Maxipago as your payment gateway and enter the required credentials:

- Rede: PV and Token
- Maxipago: Merchant ID and Merchant Key

Configure additional options like installments, 3DS authentication, and logging. Save your settings.

Note: Do not hardcode credentials in source files. Use the plugin settings or environment-safe methods.

---

## Frequently Asked Questions

Q: What is the license of the plugin?  
A: This plugin is released under the GPLv3 license.

Q: What do I need to use this plugin?  
A: You need to have the WooCommerce plugin installed and activated.

Q: Does the plugin support installment payments?  
A: Yes, you can configure installment options directly in the plugin settings.

Q: Can I test the plugin before going live?  
A: Yes — the plugin includes a Sandbox Mode for testing transactions.

Q: Is 3DS authentication supported?  
A: Yes, the plugin supports 3DS authentication for secure transactions.

Q: Can I process refunds through the plugin?  
A: Yes, refunds can be processed directly from the WooCommerce admin dashboard.

Q: Does the plugin support tokenization?  
A: Yes, the plugin allows you to save card details securely for returning customers.

Q: What payment methods are supported?  
A: Credit cards, debit cards, and PIX payments.

Q: How do I enable logging for debugging?  
A: Enable logging in the plugin settings under the Advanced tab.

Q: Is the plugin compatible with the latest WordPress version?  
A: Yes — tested up to WordPress 6.8.

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
- Fix installment values update.
- Fixed payment refunds.
- Fixed the endpoint for the PIX verification route.

### 4.0.3 - 2025/09/12
- Fix refund function.

### 4.0.2 - 2025/09/05
- Update to the sales links.
- Fix in the plugin's textdomain.
- Plugin icon in the gateway settings.

... (previous changelog entries retained) ...

---

## Screenshots

1. Payment methods list  
2. Rede Credit settings page  
3. Rede Credit front inputs page  
4. Maxipago Credit front inputs page  
5. Maxipago Credit summary page  
6. Rede Credit front page (legacy) with installments  
7. Rede Debit settings page  
8. Rede and Maxipago payment list

---

## Upgrade Notice

- 4.1.0 — Improved settings interface and updated descriptions for easier configuration.
- See ChangeLog for full details of earlier releases and fixes.

---

## Support

For any issues or questions, visit our support page or open a ticket on the WordPress plugin repository.

---

Try Integration Rede for WooCommerce Today!  
Enhance your WooCommerce store with a reliable, secure, and feature-rich payment gateway. Start accepting payments with credit cards, debit cards, and PIX today!
