---
name: prepare-release
description: Prepara release do woo-rede: atualiza versão em README.txt, CHANGELOG.md, cabeçalho PHP, constante VERSION e workflows .yml baseado no git log
---

# prepare-release (woo-rede)

Atualiza **todos** os arquivos que contêm o número de versão para uma nova release do plugin "Integration Rede Itaú for WooCommerce".

## Parâmetros (via `arguments`)

O usuário pode passar os valores diretamente: `"version=5.4.10 tested_up=7.1 php=8.2 highlights=..."`. Se algum valor faltar, pergunte.

- **version** — nova versão (Stable tag)
- **tested_up** — versão do WP testada (Tested up to)
- **php** — versão mínima do PHP (Requires PHP)
- **highlights** — resumo da versão (opcional, usa git log se vazio)

## Fluxo de execução

### 1. Coletar valores

Se não recebidos via arguments, pergunte um por um. Detecte a versão atual:

```
grep -E "Stable tag:" README.txt
grep -E "define\('INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION'" lkn-integration-rede-for-woocommerce-file.php
```

Data de hoje: use `date +%Y-%m-%d` (ou API de tempo se o shell não estiver disponível).

### 2. Capturar git log

```bash
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null)
if [ -z "$LAST_TAG" ]; then
    git log -n 10 --oneline
else
    git log ${LAST_TAG}..HEAD --oneline
fi
```

### 3. Atualizar TODOS os arquivos com versão

A versão aparece em **7 arquivos** (o `README.txt` tem 3 locais distintos).

#### 3a. `README.txt`

- `Stable tag:` → nova versão (linha ~8)
- `Tested up to:` e `Requires PHP:` se alterados
- Adicionar entrada no `## Changelog` (topo da seção), **sempre em inglês**, usando data `YYYY-MM-DD`. Deixar **uma linha em branco** entre a nova entrada e a anterior:
  ```
  ### VERSION - YYYY-MM-DD
  * Item baseado nos commits

  ### VERSAO_ANTERIOR - YYYY-MM-DD
  ```
- ⚠️ O `README.txt` tem **DUAS** listas de changelog: `## Changelog` **e** `## Upgrade Notice`. Adicionar a MESMA entrada nas duas.

#### 3b. `CHANGELOG.md`

- Adicionar entrada no topo do arquivo, **em português**, usando data `DD/MM/YY`:
  ```
  # VERSION - DD/MM/YY
  * Item baseado nos commits
  ```

#### 3c. `integration-rede-for-woocommerce.php` (raiz)

- `* Version:           X` (cabeçalho do plugin, linha ~18)
- `Requires PHP:` / `Requires at least:` se alterados

#### 3d. `lkn-integration-rede-for-woocommerce-file.php`

- `define('INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION', 'X')` (linha ~20)

#### 3e. `.github/workflows/main.yml`

- `DEPLOY_TAG: "X"`

#### 3f. `.github/workflows/dev-release.yml`

- `DEPLOY_TAG: "X"`

#### 3g. `.github/workflows/wordpressRelease.yml`

- `DEPLOY_TAG: "X"` (o `VERSION: ${{ env.DEPLOY_TAG }}` é derivado — não editar)

## NÃO alterar (pegadinhas do woo-rede)

- `Includes/LknIntegrationRedeForWoocommerce.php:133` → `$this->version = '1.0.0'` é **fallback de bootstrap** (nunca é bumpado; a versão real vem da constante `INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION`).
- `Includes/LknIntegrationRedeForWoocommerceWcRede.php:12` → `public const VERSION = '3.1.2'` é **versão de migração de DB** (`wc_rede_version`), não a versão do plugin.
- `package.json` → `version` é do dev container, não do plugin.
- `Admin/LknIntegrationRedeForWoocommerceAdmin.php:144` usa a constante, sem literal — não editar.

## 4. Validação final

Grep com a versão **antiga**:

```
grep -rn "VERSAO_ANTIGA" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```

Esperado: `CHANGELOG.md` e `README.txt` ainda contêm a versão antiga **apenas** nas entradas antigas do changelog. Qualquer outro arquivo retornando a versão antiga é **erro**.

Grep com a versão **nova**:

```
grep -rn "NOVA_VERSAO" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```

Deve retornar: `README.txt` (3x: stable tag + changelog + upgrade notice), `CHANGELOG.md` (1x), `.php` raiz (1x), constante (1x), 3 workflows (3x) = **9 matches** no mínimo.
