# AGENTS.md — Diretrizes Absolutas

Regras imutáveis. Qualquer desvio deve ser justificado no código via comentário `// REASON:`.

---

## 1. Arquitetura (SOLID + PSR-4)

### S — Single Responsibility
- 1 classe = 1 motivo para mudar.
- `LknIntegrationRedeForWoocommerce` — orquestra hooks e ciclo de vida apenas.
- `LknIntegrationRedeForWoocommerceWcRedeCredit` / `*Debit` / `*PixRede` — cada gateway tem seu próprio fluxo.
- `LknIntegrationRedeForWoocommerceHelper` — utilitários compartilhados (token, metadata, BIN, logging).
- `LknIntegrationRedeForWoocommerceWcEndpoint` — endpoints REST e webhooks apenas.
- Funções com >40 linhas: quebrar ou justificar.

### O — Open/Closed
- Extensão via hooks, nunca via edição de código existente.
- `apply_filters('integration_rede_for_woocommerce_*', $value, $context)` em todo ponto de extensão.
- `do_action('integration_rede_for_woocommerce_*', $data)` em todo ponto de ciclo de vida.
- Plugin PRO estende via filtros — manter pontos de integração limpos.

### L — Liskov Substitution
- Todos os gateways estendem `WC_Payment_Gateway`; Credit e Debit estendem `LknIntegrationRedeForWoocommerceWcRedeAbstract`.
- Subclasse deve passar nos mesmos contratos da classe pai (`process_payment`, `process_refund`, `process_admin_options`).

### I — Interface Segregation
- Blocks integrations em classes `*Blocks.php` dedicadas — não colocar lógica de blocos no gateway principal.

### D — Dependency Inversion
- Gateways recebem config via `get_option()` / `$this->get_option()`, nunca hardcoded.
- OAuth token delegado ao `Helper` — gateways não chamam API da Rede diretamente para auth.

### PSR-4
```
Lknwoo\IntegrationRedeForWoocommerce\Includes\  → Includes/
Lknwoo\IntegrationRedeForWoocommerce\Admin\      → Admin/
Lknwoo\IntegrationRedeForWoocommerce\PublicView\  → Public/
```
- 1 classe por arquivo. Nome do arquivo = nome da classe.
- Namespace deve corresponder ao caminho do diretório.

---

## 2. Segurança

### Superglobais — sanitizar SEMPRE
```php
// Proibido
$id = $_GET['id'];

// Obrigatório
$id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
```

### Nonces — toda requisição state-changing
```php
if (!isset($_POST['rede_card_nonce']) || !wp_verify_nonce($_POST['rede_card_nonce'], 'redeCardNonce')) {
    // fail
}
```

### Output escaping
```php
echo esc_html($value);       // HTML context
echo esc_attr($value);       // Attribute context
echo esc_url($url);          // URL context
echo wp_kses_post($html);    // Late escaping
```

### SQL — prepared statements
```php
// Proibido
$wpdb->query("SELECT * FROM $wpdb->postmeta WHERE meta_key = '$key'");

// Obrigatório
$wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", $key);
```

### API credentials
- Nunca expor tokens, PV ou chaves de API no frontend ou em logs públicos.
- Log debug apenas quando `$this->debug === 'yes'`.
- Nunca expor stack traces para o frontend.

---

## 3. Padrões WordPress / WooCommerce

### Naming
- Classes: `LknIntegrationRedeForWoocommerce{Nome}`
- Funções/hooks: `lkn_integration_rede_*` ou `lkn_rede_*`
- Meta keys: `_wc_rede_*` (private) ou `lkn_rede_*` (public)
- Session keys: `lkn_*`
- Options: `woocommerce_{gateway_id}_settings`

### Internacionalização
- Toda string visível ao usuário: `__()`, `_e()`, `_n()`
- Text domain: `woo-rede`

### Order Notes
- Todo `$order->add_order_note()` deve prefixar com `'[' . $this->id . '] '` (gateway) ou `'[' . $gateway_id . '] '` (outros contextos).

### Order Status
- `process_order_status_v2()` e `process_3ds_order_status()`: atualizar status incondicionalmente baseado no returnCode.
- PIX webhook handlers: pode guardar com `has_status('pending')` pois múltiplos callbacks podem ocorrer.

### Database
- Preferir API de order meta (`get_meta`/`update_meta_data`) sobre queries diretas em `postmeta`.
- Limpar dados do plugin em `uninstall.php`.

### Assets
- `wp_enqueue_script()` / `wp_enqueue_style()` com `INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION`.
- `wp_localize_script()` para dados PHP → JS.

### Transients / Cache
- OAuth tokens cacheados via `LknIntegrationRedeForWoocommerceHelper`.
- Evitar chamadas redundantes à API da Rede no mesmo request lifecycle.

---

## 4. Tratamento de Erros

- Lançar `LknIntegrationRedeForWoocommerceTransactionException` para erros de transação de pagamento.
- Order notes com detalhes relevantes em caso de falha (returnCode, returnMessage).
- Bloco try/catch em todo fluxo de pagamento com fallback adequado.

---

## 5. Gateways

| Gateway ID | Classe | 3DS? |
|---|---|---|
| `rede_debit` | `WcRedeDebit` | ✅ (débito + crédito com 3DS) |
| `rede_credit` | `WcRedeCredit` | ❌ (crédito puro) |
| `rede_google_pay` | `GooglePay` | ❌ |
| `integration_rede_pix` | `WcPixRede` | ❌ |
| `maxipago_credit` | `WcMaxipagoCredit` | ❌ |
| `maxipago_debit` | `WcMaxipagoDebit` | ❌ |

### 3DS Flow (apenas `rede_debit`)
1. `process_payment()` → `is_paid()` check → envia transação com `threeDSecure` no body.
2. Rede retorna `threeDSecure.url` → redireciona cliente pro banco.
3. Callback: `POST /wp-json/woorede/s` (sucesso) ou `/f` (falha).
4. `handle3dsSuccess()`: `validate_webhook_security()` → `$order->is_paid()` → estorno automático se duplicata.
5. `handle3dsFailure()`: mantém status `pending` para retry.

### Proteção Anti-Duplicata
- `process_payment()`: `$order->is_paid()` → `Duplicate order attempt.` + exceção.
- `handle3dsSuccess()`: `$order->is_paid()` → order note + `refund_duplicate_transaction($tid, $order->get_total())`.
- Aplica-se a todos os gateways via `is_paid()` check.

---

## 6. Build & Qualidade

```bash
npm install && composer install   # setup
npm run build                      # assets
```

---

## 7. Comunicação (Caveman Mode + RTK)

### Caveman Mode — ATIVO
- Zero saudações. Zero "claro!", "ótimo!", "vamos lá!".
- Zero resumos pós-entrega.
- Frases curtas. Sem períodos compostos.
- Código > prosa. Sempre.

### RTK (Rust Token Killer) — ATIVO
- Logs de terminal são comprimidos pelo RTK antes de chegar ao LLM.
- Nunca solicitar output verboso se snippet RTK estruturado já foi fornecido.
- Confiar no pré-parsing do RTK.

### Formato de resposta esperado
```
Tipo: [fix|feat|refactor|security]
Arquivo: path/to/file.php:123
Problema: descrição ≤1 linha
Solução: descrição ≤1 linha
---
[código/diff]
```
