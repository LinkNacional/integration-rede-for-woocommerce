# Integration Rede Itaú for WooCommerce — Development Guidelines

## Plugin Overview

- **Plugin**: `integration-rede-for-woocommerce` (slug: `woo-rede`)
- **Stack**: PHP 8.2+, WordPress 6.8+, WooCommerce, React (Blocks)
- **Text Domain**: `woo-rede`
- **Prefix**: `LknIntegrationRedeForWoocommerce` (classes), `lkn_` (functions/hooks)
- **Gateways**: `rede_credit`, `rede_debit`, `integration_rede_pix`, `rede_pix`, `rede_google_pay`, `maxipago_credit`, `maxipago_debit`

## Architecture & SOLID Principles

**Single Responsibility Principle (SRP)**
- `LknIntegrationRedeForWoocommerce` — orchestrates hooks and plugin lifecycle only
- `LknIntegrationRedeForWoocommerceWcRedeCredit` / `*Debit` / `*PixRede` — each gateway owns its own payment flow
- `LknIntegrationRedeForWoocommerceHelper` — shared utilities (token, metadata, BIN, logging)
- `LknIntegrationRedeForWoocommerceWcEndpoint` — REST endpoint and webhook handling only
- `LknIntegrationRedeForWoocommerceAdmin` / `*Public` — admin vs. frontend concerns
- Do not add unrelated logic to existing classes; create a new class if needed

**Open/Closed Principle (OCP)**
- Extend gateway behavior through WooCommerce hooks and custom filters, never by patching core flow
- Use `apply_filters('integration_rede_for_woocommerce_*', ...)` and `do_action('integration_rede_for_woocommerce_*', ...)` for extensibility
- The PRO plugin extends FREE plugin via filters — keep integration points clean and documented

**Liskov Substitution Principle (LSP)**
- All gateways extend `WC_Payment_Gateway`; `*Credit` and `*Debit` both extend `LknIntegrationRedeForWoocommerceWcRedeAbstract`
- Subclasses must not break the contract of `process_payment()`, `process_refund()`, or `process_admin_options()`

**Interface Segregation Principle (ISP)**
- Block integrations live in dedicated `*Blocks.php` classes (e.g., `LknIntegrationRedeForWoocommerceWcRedeCreditBlocks`)
- Do not add Blocks logic to the main gateway class

**Dependency Inversion Principle (DIP)**
- Gateway classes receive configuration via `get_option()` / `$this->get_option()`, not hardcoded values
- OAuth token retrieval is delegated to `LknIntegrationRedeForWoocommerceHelper` — gateways must not call the Rede API directly for auth

## Code Standards

**WordPress / WooCommerce**
- PHP 8.2+, WordPress Coding Standards
- Always use `wp_verify_nonce()` on form submissions and AJAX handlers
- Sanitize all inputs: `sanitize_text_field()`, `absint()`, `wp_unslash()`, etc.
- Escape all outputs: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Internationalize every user-facing string with text domain `woo-rede`: `__('text', 'woo-rede')`

**Naming Conventions**
- Classes: `LknIntegrationRedeForWoocommerce*`
- Functions / hooks: `lkn_integration_rede_*` or `lkn_rede_*`
- Meta keys: `_wc_rede_*` (private) or `lkn_rede_*` (public)
- Session keys: `lkn_*`
- Options: `woocommerce_{gateway_id}_settings`, `lkn_*`

**Order Notes**
- Every `$order->add_order_note()` call must prefix the message with `'[' . $this->id . '] '` (gateway classes) or `'[' . $gateway_id . '] '` (non-gateway contexts)
- This marker is consumed by `add_gateway_name_to_notes_global()` to prepend the gateway title; notes without the marker are left untouched (third-party notes)

**Order Status Updates**
- `process_order_status_v2()` and `process_3ds_order_status()` must update the order status unconditionally based on the return code — do not guard with `has_status('pending')`
- PIX webhook handlers may guard with `has_status('pending')` because multiple webhook calls can occur

## Project Structure

```
Admin/                          # Admin-only assets and partials
Includes/
  LknIntegrationRedeForWoocommerce.php          # Core: hook orchestration
  LknIntegrationRedeForWoocommerceHelper.php    # Shared utilities
  LknIntegrationRedeForWoocommerceWcRedeAbstract.php  # Base for credit/debit
  LknIntegrationRedeForWoocommerceWcRedeCredit.php
  LknIntegrationRedeForWoocommerceWcRedeDebit.php
  LknIntegrationRedeForWoocommerceWcPixRede.php
  LknIntegrationRedeForWoocommerceGooglePay.php
  LknIntegrationRedeForWoocommerceWcMaxipagoCredit.php
  LknIntegrationRedeForWoocommerceWcMaxipagoDebit.php
  LknIntegrationRedeForWoocommerceWcEndpoint.php  # REST / webhooks
  LknIntegrationRedeForWoocommerce*Blocks.php     # Block checkout integrations
Public/                         # Frontend-only assets
languages/                      # .pot / .po files (text domain: woo-rede)
uninstall.php                   # Cleanup on uninstall
```

## Build Commands

```bash
# Install dependencies
npm install && composer install

# Build assets
npm run build
```

## WordPress Plugin Specifics

**Hooks Priority**
- Default priority (10) unless a specific order with WooCommerce hooks is required
- Document non-default priorities inline

**Database Operations**
- Use `$wpdb->prepare()` for all custom queries
- Prefer WooCommerce order meta API (`get_meta` / `update_meta_data`) over direct `postmeta` queries
- Clean up all plugin data in `uninstall.php`

**Asset Management**
- Enqueue with `wp_enqueue_script()` / `wp_enqueue_style()` using `INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION` as version

## Error Handling

- Throw `LknIntegrationRedeForWoocommerceTransactionException` for payment transaction errors
- Log debug information only when `$this->debug === 'yes'`
- Never expose API credentials or raw stack traces to the frontend

## Performance

- Cache OAuth tokens using WordPress transients via `LknIntegrationRedeForWoocommerceHelper`
- Avoid redundant API calls; reuse token data within a single request lifecycle
- Do not load admin-only classes on the frontend