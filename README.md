# Integration Rede for WooCommerce — Advanced Technical README

This README provides a technical, developer-oriented and user-facing reference for the "Integration Rede for WooCommerce" plugin. It complements the user-facing docs and describes installation, configuration, runtime behaviour, troubleshooting, developer extension points and recommendations for production deployment.

Table of contents
- Overview
- Requirements & Support
- Installation (UI, WP-CLI, Manual/FTP)
- Configuration (fields explained)
- Runtime behaviour and Checkout flow
- Admin operations (capture, refund, void, order mapping)
- Webhooks / Notifications
- Security, PCI and TLS guidance
- Logging, Debugging & Troubleshooting
- Testing & Sandbox
- Developer guide (hooks, filters, plugin structure)
- Changelog & Contribution
- License

Overview
The plugin integrates Rede (and optionally Maxipago) payment processing into WooCommerce, allowing credit/debit card payments and supporting typical card flows (authorize, capture, authorize+capture, refunds). It supports environment selection (Sandbox/Production), full logging for debug, and configurable mapping between payment outcomes and WooCommerce order statuses.

Requirements & support
- PHP: 7.4 — 8.1 (recommend 8.0+)
- WordPress: 6.0+
- WooCommerce: 4.0+ (recommend latest stable release)
- HTTPS (TLS 1.2+) for any production environment that exchanges payment data
- cURL and OpenSSL available on the server (most PHP builds include these)
- Supported gateways: Rede and Maxipago (credentials for at least one gateway required)

Installation

1) Via WordPress Admin (recommended)
- Dashboard -> Plugins -> Add New -> Upload Plugin
- Select the plugin ZIP (integration-rede-for-woocommerce-master.zip)
- Install and Activate
- Visit WooCommerce -> Settings -> Payments (or Payments -> Rede) to configure

2) Via WP-CLI
- Upload the plugin folder to wp-content/plugins/
- Activate: wp plugin activate integration-rede-for-woocommerce

3) Manual / FTP
- Extract archive into wp-content/plugins/integration-rede-for-woocommerce
- Ensure file permissions allow PHP to read plugin files
- Activate in WordPress admin -> Plugins

Configuration
Open WooCommerce -> Settings -> Payments -> Rede (or WooCommerce -> Settings -> Payments and click Rede/Maxipago). The plugin configuration page exposes these fields:

General
- Enabled: toggle to enable payments via this gateway
- Title: text shown to customers on checkout (default: Rede)
- Description: short description shown under the payment method

Environment & Credentials
- Environment: Sandbox / Production
- Rede PV (Point of Sale): numeric/text credential required by Rede (sandbox/production values differ)
- Rede Token: API token for Rede
- Maxipago Merchant ID: numeric identifier (if using Maxipago)
- Maxipago Merchant Key: API key/secret
- Endpoint URLs: read-only display for Sandbox and Production API endpoints

Transaction Settings
- Transaction Mode: Authorize only / Authorize & Capture (if your business captures later)
- Capture on order status: controls automatic capture (e.g., on "processing" vs "completed")
- Allow partial capture: enable partial capture for split shipments (if supported)
- Allow refunds via gateway: enable to allow refunds from WooCommerce admin

Cards & UX
- Save cards (tokenization): enable storing tokens for returning customers (requires gateway support and local consent UI)
- 3DS / Strong Customer Authentication: enable/disable 3DS flow when supported
- Installments: enable and configure maximum number of interest-free installments (if gateway supports)

Advanced / Integration
- Webhook / Notification URL: the webhook URL to register with Rede/Maxipago (copy from plugin settings)
- Webhook secret / signature validation key: if gateway provides signature for notifications
- Order status mapping: map gateway statuses (authorized, captured, refunded, disputed) to WooCommerce order statuses
- Logging: Disabled / Error only / Debug — log file location shown in settings
- Timeout & Retry: HTTP request timeout and retry attempts for API calls

Mode of use — Customer flow
1. Customer selects Rede (or Maxipago) at checkout.
2. Customer enters card details (or chooses saved card token if available).
3. Plugin performs API call to gateway:
   - If Authorize & Capture: creates payment and captures immediately.
   - If Authorize only: creates an authorization; capture must be performed later.
4. Gateway responds with success/failure:
   - Success: order status updated according to configuration, transaction ID stored in order meta.
   - Failure: order checkout returns an error message; no order is created, or an order with failed payment is created depending on settings.
5. For asynchronous flows (3DS, redirect): the plugin waits for return/webhook to finalize the payment.

Admin operations (capture, refund, void)
- Capture: If using Authorize-only, capture can be done from the WooCommerce order screen (Payment actions -> Capture). The plugin issues a capture request to the gateway and updates order meta and status.
- Refund: Refunds initiated from WooCommerce admin will call the gateway refund endpoint (if "Allow refunds via gateway" is enabled). Partial refunds are supported when the gateway allows them.
- Void / Cancel: If a transaction is authorized but not captured, a void request can cancel the authorization. Mapping to order statuses is configurable.

Webhooks / Notifications
- Purpose: receive asynchronous payment notifications (capture, refund, dispute, chargeback).
- Registration: register the plugin-provided webhook URL in Rede/Maxipago dashboard or via API.
- Endpoint requirement:
  - Must be HTTPS and publicly reachable.
  - Use the URL provided in plugin settings (copy/paste to gateway dashboard).
  - Gateway notifications include a payload and an HMAC/signature header when configured — plugin validates signature if secret provided.
- Processing:
  - On webhook receipt, plugin validates signature and payload, maps event to an order using transaction/order identifiers, and updates status (capture/refund/etc).
  - Idempotency: plugin tracks webhook delivery IDs to avoid processing duplicates.

Security, PCI & TLS
- Card data handling: The plugin is designed to avoid storing raw card numbers. If you enable tokenization, only gateway tokens should be stored.
- PCI scope: For PCI-DSS minimization, prefer a hosted payment page or direct tokenization provided by the gateway. If the plugin collects card data on-site, your site may fall into a higher PCI SAQ requirement.
- TLS: Use TLS 1.2 or newer for all gateway API calls and webhook endpoints.
- Secrets: Keep PV, Token and Merchant Key values secret. Do not commit them to version control.
- File permissions: plugin files should be readable by web server and protected from direct public write access.

Logging, Debugging & Troubleshooting
- Enable "Debug" logging in plugin settings only on staging/dev systems. Logs contain request/response payloads and error traces.
- Log location: shown on the plugin settings page — typically in wp-content/uploads/integration-rede/logs/ or WooCommerce logs (WooCommerce -> Status -> Logs).
- Common debug steps:
  - Verify credentials and environment (sandbox vs production).
  - Ensure webhook endpoint reachable (use curl or ngrok for local testing).
  - Reproduce with plugin logging enabled and examine API request/response.
  - Check PHP error logs and WooCommerce status report for cURL/OpenSSL issues.
- Useful commands (from plugin host, Linux):
  - Test webhook reachability: curl -v -X POST https://your-site.example/rede-webhook -d '{"test":"1"}'
  - Check TLS versions: openssl s_client -connect api.rede.example:443

Testing & Sandbox
- Use Sandbox credentials provided by Rede/Maxipago to run test transactions.
- Test card numbers: use gateway-provided test card numbers where available (only use official test numbers from the gateway documentation).
- Steps to test:
  1. Switch plugin to Sandbox environment.
  2. Configure Sandbox PV/Token or Merchant ID/Key.
  3. Place test orders and validate success, 3DS, declines, refunds, webhook events.
- Automated tests: writing PHPUnit tests for API client classes and webhook handlers is recommended. Mock HTTP client responses and test order state transitions.

Developer guide (extending the plugin)
- Plugin structure (typical)
  - includes/: API client, helpers, webhooks, admin handlers
  - assets/: JS/CSS for checkout/3DS
  - templates/: optional templates for checkout
  - languages/: translation files (.pot/.po/.mo)
- Key extension points (examples — adapt to actual plugin hook names)
  - Filters:
    - rei_rede_request_payload (array $payload, WC_Order $order): modify outgoing API payload
    - rei_rede_capture_args (array $args, WC_Order $order): modify capture parameters
  - Actions:
    - rei_rede_after_payment (WC_Order $order, array $gateway_response): custom post-processing after successful payment
    - rei_rede_webhook_received (array $payload): triggered upon validated webhook
- Best practices:
  - Use WooCommerce CRUD (wc_get_order, $order->update_status) for order updates.
  - Use wp_remote_post with proper timeouts and retries for HTTP calls.
  - Respect WooCommerce capabilities and roles for admin actions.
  - Escape and sanitize all outputs and inputs (esc_html, sanitize_text_field, wp_verify_nonce).
- Internationalization:
  - Use textdomain 'integration-rede' (or plugin textdomain) and prepare .pot file in languages/.

Order meta & storage
- The plugin stores gateway transaction details in order meta for traceability:
  - _rede_transaction_id
  - _rede_payment_status
  - _rede_raw_response (may be logged if debug enabled)
- Use these meta keys from custom code to fetch gateway results.

Changelog & contribution
- Contributing: submit PRs or issues via the repository. Follow code style, include unit tests for new features and ensure compatibility with supported PHP and WordPress versions.
- Branching: use feature branches and include a clear changelog entry.
- Tests: add PHPUnit tests and use GitHub Actions for CI (if configured).

Troubleshooting common issues
- "Payment declined" in sandbox: confirm test card numbers and sandbox credentials.
- "Webhook not processed": ensure the plugin webhook URL is registered and reachable, check for signature mismatch if secret configured.
- "cURL/OpenSSL errors": ensure PHP cURL and OpenSSL are installed and up-to-date on the server.

Example quick-check list for production deployment
- [ ] Use Production credentials and switch Environment to Production
- [ ] Ensure HTTPS with valid certificate (no self-signed)
- [ ] Configure webhook URL in gateway dashboard and verify delivery
- [ ] Enable logging temporarily and perform smoke-test transactions
- [ ] Validate refund and capture flows
- [ ] Harden permissions and backups

License
- This project is distributed under the same license included with the plugin package (check LICENSE file). Please do not publish or distribute production credentials.

Contact & Support
- For user-facing support: open plugin support ticket on WordPress.org plugin page or open an issue in this repository.
- For development questions: open an issue and tag maintainers.

Appendix — Useful curl examples
- Test webhook endpoint (local example):
  curl -v -X POST https://your-store.example/rede-webhook -H "Content-Type: application/json" -d '{"event":"test"}'
- Fetch WooCommerce order via REST (admin usage):
  curl -u consumer_key:consumer_secret "https://your-store.example/wp-json/wc/v3/orders/1234"

Notes
- This README is intentionally technical and generic enough to cover typical integration scenarios. Always consult Rede/Maxipago official API docs for specific request/response formats, test card numbers, and sandbox behavior.
