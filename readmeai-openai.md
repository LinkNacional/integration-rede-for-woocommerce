<div id="top">

<!-- HEADER STYLE: CLASSIC -->
<div align="center">

<img src="readmeai/assets/logos/purple.svg" width="30%" style="position: relative; top: 0; right: 0;" alt="Project Logo"/>

# INTEGRATION-REDE-FOR-WOOCOMMERCE

<em></em>

<!-- BADGES -->
<img src="https://img.shields.io/github/license/LinkNacional/integration-rede-for-woocommerce?style=default&logo=opensourceinitiative&logoColor=white&color=0080ff" alt="license">
<img src="https://img.shields.io/github/last-commit/LinkNacional/integration-rede-for-woocommerce?style=default&logo=git&logoColor=white&color=0080ff" alt="last-commit">
<img src="https://img.shields.io/github/languages/top/LinkNacional/integration-rede-for-woocommerce?style=default&color=0080ff" alt="repo-top-language">
<img src="https://img.shields.io/github/languages/count/LinkNacional/integration-rede-for-woocommerce?style=default&color=0080ff" alt="repo-language-count">

<!-- default option, no dependency badges. -->


<!-- default option, no dependency badges. -->

</div>
<br>

---

## Table of Contents

- [Table of Contents](#table-of-contents)
- [Overview](#overview)
- [Features](#features)
- [Project Structure](#project-structure)
    - [Project Index](#project-index)
- [Getting Started](#getting-started)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
    - [Usage](#usage)
    - [Testing](#testing)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)
- [Acknowledgments](#acknowledgments)

---

## Overview



---

## Features

|      | Component       | Details                              |
| :--- | :-------------- | :----------------------------------- |
| ⚙️  | **Architecture**  | <ul><li>Follows a modular architecture with separate components for payment integration, localization, and frontend logic.</li><li>Uses a combination of PHP for backend logic and JavaScript for frontend interactions.</li></ul> |
| 🔩 | **Code Quality**  | <ul><li>Consistent code formatting and style enforced using ESLint with Airbnb and Standard configurations.</li><li>Webpack used for bundling JavaScript modules.</li></ul> |
| 📄 | **Documentation** | <ul><li>Currently lacking detailed documentation within the codebase.</li></ul> |
| 🔌 | **Integrations**  | <ul><li>Integrates with WooCommerce for e-commerce functionality.</li><li>Uses React components for credit card form handling.</li></ul> |
| 🧩 | **Modularity**    | <ul><li>Codebase structured into separate directories for PHP backend, JavaScript frontend, and CSS styling.</li><li>Reusable components for checkout process.</li></ul> |
| 🧪 | **Testing**       | <ul><li>Unit tests missing in the codebase.</li><li>Potential for adding Jest or PHPUnit tests for backend and frontend components.</li></ul> |
| ⚡️  | **Performance**   | <ul><li>Optimized asset loading using Webpack bundling.</li><li>Potential for performance improvements through code profiling and optimization.</li></ul> |
| 🛡️ | **Security**      | <ul><li>No evident security vulnerabilities in the codebase.</li><li>Secure handling of credit card information using React components.</li></ul> |
| 📦 | **Dependencies**  | <ul><li>Relies on various npm packages for frontend development (e.g., React, ESLint).</li><li>Composer used for PHP dependencies.</li></ul> |

---

## Project Structure

```sh
└── integration-rede-for-woocommerce/
    ├── .github
    │   ├── ISSUE_TEMPLATE
    │   ├── pull_request_template.md
    │   └── workflows
    ├── Admin
    │   ├── LknIntegrationRedeForWoocommerceAdmin.php
    │   ├── css
    │   ├── images
    │   ├── index.php
    │   ├── js
    │   └── partials
    ├── CHANGELOG.md
    ├── Includes
    │   ├── LknIntegrationRedeForWoocommerce.php
    │   ├── LknIntegrationRedeForWoocommerceActivator.php
    │   ├── LknIntegrationRedeForWoocommerceDeactivator.php
    │   ├── LknIntegrationRedeForWoocommerceHelper.php
    │   ├── LknIntegrationRedeForWoocommerceLoader.php
    │   ├── LknIntegrationRedeForWoocommerceTransactionException.php
    │   ├── LknIntegrationRedeForWoocommerceWcEndpoint.php
    │   ├── LknIntegrationRedeForWoocommerceWcMaxipagoCredit.php
    │   ├── LknIntegrationRedeForWoocommerceWcMaxipagoCreditBlocks.php
    │   ├── LknIntegrationRedeForWoocommerceWcMaxipagoDebit.php
    │   ├── LknIntegrationRedeForWoocommerceWcMaxipagoDebitBlocks.php
    │   ├── LknIntegrationRedeForWoocommerceWcPixHelper.php
    │   ├── LknIntegrationRedeForWoocommerceWcPixRede.php
    │   ├── LknIntegrationRedeForWoocommerceWcPixRedeBlocks.php
    │   ├── LknIntegrationRedeForWoocommerceWcRede.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeAPI.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeAbstract.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeCredit.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeCreditBlocks.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeDebit.php
    │   ├── LknIntegrationRedeForWoocommerceWcRedeDebitBlocks.php
    │   ├── assets
    │   ├── files
    │   ├── index.php
    │   ├── templates
    │   └── views
    ├── LICENSE.txt
    ├── Public
    │   ├── LknIntegrationRedeForWoocommercePublic.php
    │   ├── css
    │   ├── images
    │   ├── index.php
    │   ├── js
    │   └── partials
    ├── README.md
    ├── README.txt
    ├── composer.json
    ├── composer.lock
    ├── index.php
    ├── integration-rede-for-woocommerce.php
    ├── languages
    │   ├── integration-rede-for-woocommerce-pt_BR.po
    │   └── lkn-integration-rede-for-woocommerce.pot
    ├── lkn-integration-rede-for-woocommerce-file.php
    ├── package-lock.json
    ├── package.json
    ├── uninstall.php
    └── webpack.config.js
```

### Project Index

<details open>
	<summary><b><code>INTEGRATION-REDE-FOR-WOOCOMMERCE/</code></b></summary>
	<!-- __root__ Submodule -->
	<details>
		<summary><b>__root__</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ __root__</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/integration-rede-for-woocommerce.php'>integration-rede-for-woocommerce.php</a></b></td>
					<td style='padding: 8px;'>- Initiate the plugin, register functions, and define plugin information for Integration Rede for WooCommerce<br>- Receive credit and debit card payments with 3DS authentication and fraud protection<br>- The file serves as the plugin bootstrap, handling dependencies, activation, deactivation, and plugin initiation.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/uninstall.php'>uninstall.php</a></b></td>
					<td style='padding: 8px;'>- Uninstall script that cleans up plugin settings upon removal<br>- Ensures proper deletion of specific options related to WooCommerce payment gateways<br>- Follows a structured flow to verify plugin name, authenticate, and handle different user roles<br>- Designed to be updated in future versions of the Boilerplate.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/composer.json'>composer.json</a></b></td>
					<td style='padding: 8px;'>Define the integration structure for WooCommerce with Rede, including autoload configurations and required dependencies.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/lkn-integration-rede-for-woocommerce-file.php'>lkn-integration-rede-for-woocommerce-file.php</a></b></td>
					<td style='padding: 8px;'>- Initialize and manage the integration of Rede for WooCommerce plugin<br>- Define essential constants, activate and deactivate plugin actions, and execute plugin functionality seamlessly<br>- Ensure smooth operation without disrupting the page life cycle.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/README.txt'>README.txt</a></b></td>
					<td style='padding: 8px;'>- Enable customers to pay via credit or debit cards using 3DS authentication with the Integration Rede for WooCommerce plugin<br>- Integrate Rede or Maxipago into your WooCommerce store effortlessly, offering secure payment processing for major card brands in Brazil<br>- Simply configure payment gateway settings in WooCommerce to start accepting payments seamlessly.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/LICENSE.txt'>LICENSE.txt</a></b></td>
					<td style='padding: 8px;'>- SummaryThe provided <code>LICENSE.txt</code> file contains the GNU General Public License, Version 2, June 1991<br>- This license ensures that the software within the project is free for all its users, guaranteeing the freedom to share and modify the code<br>- By adhering to this license, the project maintains a commitment to open-source principles, allowing users to freely distribute and modify the software while protecting their rights to access the source code<br>- This license promotes a collaborative and inclusive environment for the projects community, emphasizing the importance of software freedom.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/package.json'>package.json</a></b></td>
					<td style='padding: 8px;'>- Create a development environment for WHMCS or WordPress with this repository<br>- It sets up a Docker-based PHP environment for plugin development, tailored for use with VS Code<br>- The package includes essential dependencies for building and linting code.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/index.php'>index.php</a></b></td>
					<td style='padding: 8px;'>Initiates the project and serves as the entry point for execution.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/package-lock.json'>package-lock.json</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>package-lock.json</code> file in the <code>docker-php-dev-container</code> project serves as a lockfile that ensures consistent dependency resolution for the project<br>- It captures the exact versions of all dependencies, including both production and development dependencies, to guarantee reproducible builds across different environments<br>- By managing the dependency tree and versions, this file helps maintain the stability and reliability of the project's codebase architecture.## Project StructureThe project follows a standard structure with the <code>package-lock.json</code> file residing at the root level<br>- The lockfile includes metadata such as the project name, version, license information, and a detailed list of dependencies and their respective versions<br>- This structured approach aids in managing dependencies effectively and streamlining the development process.By leveraging the information stored in the <code>package-lock.json</code> file, developers can easily track and control the projects dependencies, ensuring seamless collaboration and deployment<br>- This file plays a crucial role in maintaining the integrity of the project's architecture by providing a snapshot of the dependency graph at a specific point in time.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/webpack.config.js'>webpack.config.js</a></b></td>
					<td style='padding: 8px;'>- Configure Webpack to bundle and optimize JavaScript and CSS files for WooCommerce checkout integrations with Maxipago and Rede payment gateways<br>- Set production mode, define entry points, and use Babel for JavaScript transpilation<br>- Organize output filenames and paths dynamically based on entry points.</td>
				</tr>
			</table>
		</blockquote>
	</details>
	<!-- Includes Submodule -->
	<details>
		<summary><b>Includes</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ Includes</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeCreditBlocks.php'>LknIntegrationRedeForWoocommerceWcRedeCreditBlocks.php</a></b></td>
					<td style='padding: 8px;'>- Implement a WooCommerce payment method for Rede credit card transactions<br>- Handles initialization, checks availability, enqueues styles/scripts, and provides payment method data like installment options and translations<br>- Integrates seamlessly with WooCommerce Blocks for a smooth checkout experience.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeDebit.php'>LknIntegrationRedeForWoocommerceWcRedeDebit.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceWcRedeDebit.php</code> file in the <code>Lkn\IntegrationRedeForWoocommerce\Includes</code> namespace is a crucial component of the project<br>- It defines a class that handles payment integration specifically for Rede Debit within the WooCommerce platform<br>- This class sets up the necessary configurations and functionalities to enable customers to pay using Rede Debit, providing a seamless payment experience within the WooCommerce ecosystem.This file plays a vital role in expanding the payment options available to customers using the WooCommerce platform by integrating the Rede Debit payment method<br>- It encapsulates the logic required to process payments, handle refunds, and configure the necessary settings for Rede Debit transactions<br>- By leveraging this class, developers can enhance the checkout experience for users by offering them the option to pay using Rede Debit.Overall, the <code>LknIntegrationRedeForWoocommerceWcRedeDebit.php</code> file contributes significantly to the projects architecture by extending the payment capabilities of WooCommerce to include Rede Debit, thereby enriching the overall functionality and versatility of the e-commerce platform.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcMaxipagoDebit.php'>LknIntegrationRedeForWoocommerceWcMaxipagoDebit.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceWcMaxipagoDebit.php</code> file within the project's architecture is responsible for integrating and enabling payments with Maxipago Debit for WooCommerce<br>- It sets up the necessary configurations, fields, and properties required to facilitate payments using Maxipago Debit<br>- This file plays a crucial role in expanding the payment options available within the WooCommerce platform by adding support for Maxipago Debit transactions.By utilizing this file, developers can enhance the functionality of their WooCommerce stores by incorporating Maxipago Debit as a payment method, providing customers with more choices during the checkout process.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeAPI.php'>LknIntegrationRedeForWoocommerceWcRedeAPI.php</a></b></td>
					<td style='padding: 8px;'>- Manage transactions seamlessly with the LknIntegrationRedeForWoocommerceWcRedeAPI<br>- This class facilitates credit and debit card requests, transaction consultations, cancellations, and captures<br>- It integrates with eRede for secure processing and logging<br>- Simplify payment operations within your WooCommerce environment effortlessly.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceActivator.php'>LknIntegrationRedeForWoocommerceActivator.php</a></b></td>
					<td style='padding: 8px;'>- Activate scheduled updates for Rede orders upon plugin activation<br>- This class ensures timely execution of essential tasks by scheduling events in WordPress.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcPixHelper.php'>LknIntegrationRedeForWoocommerceWcPixHelper.php</a></b></td>
					<td style='padding: 8px;'>- Implement functions to handle PIX payments and refunds for WooCommerce orders<br>- The code interacts with the Rede API to process transactions based on the environment (production or sandbox)<br>- It generates PIX payment QR codes, handles refunds, and logs transaction details for debugging<br>- Additionally, it provides a method to create meta tables for orders.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcMaxipagoDebitBlocks.php'>LknIntegrationRedeForWoocommerceWcMaxipagoDebitBlocks.php</a></b></td>
					<td style='padding: 8px;'>- Implementing a custom payment method for WooCommerce Blocks, the code defines the integration with Maxipago Debit<br>- It initializes settings, registers scripts, and provides payment method data for seamless checkout experience<br>- This class extends WooCommerce Blocks AbstractPaymentMethodType, enhancing the projects payment processing capabilities.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceLoader.php'>LknIntegrationRedeForWoocommerceLoader.php</a></b></td>
					<td style='padding: 8px;'>- Registers and manages actions and filters for the WordPress plugin<br>- Maintains hooks and executes them when the plugin loads<br>- Handles adding actions and filters with specified priorities and arguments<br>- Centralizes registration of actions and filters for seamless integration with WordPress.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceHelper.php'>LknIntegrationRedeForWoocommerceHelper.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceHelper</code> class in the <code>LknIntegrationRedeForWoocommerce</code> namespace provides essential methods for interacting with the WooCommerce cart and updating specific options related to payment gateways<br>- This class includes functions to retrieve the total cart amount and to update the fix load script option based on user input<br>- By encapsulating these functionalities, it contributes to the seamless integration of Rede payment gateway features within the WooCommerce ecosystem.---If you need further details or have specific questions about the technical implementation or usage of these methods, feel free to ask!</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcPixRedeBlocks.php'>LknIntegrationRedeForWoocommerceWcPixRedeBlocks.php</a></b></td>
					<td style='padding: 8px;'>- Implement a WooCommerce payment method integration for Rede, enabling customers to pay using PIX<br>- Handles payment method initialization, availability, script enqueuing, and data retrieval for seamless checkout experience.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/index.php'>index.php</a></b></td>
					<td style='padding: 8px;'>Define the projects entry point and establish the initial server configuration.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceTransactionException.php'>LknIntegrationRedeForWoocommerceTransactionException.php</a></b></td>
					<td style='padding: 8px;'>Define a custom exception class for WooCommerce transactions, enhancing error handling by including additional data.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerce.php'>LknIntegrationRedeForWoocommerce.php</a></b></td>
					<td style='padding: 8px;'>Linknacional.com.br).---This summary provides a high-level overview of the purpose and functionality of the <code>LknIntegrationRedeForWoocommerce.php</code> file within the <code>LknIntegrationRedeForWoocommerce</code> plugin.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcPixRede.php'>LknIntegrationRedeForWoocommerceWcPixRede.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceWcPixRede.php</code> file, located in the <code>Includes</code> directory of the project, defines a class <code>LknIntegrationRedeForWoocommerceWcPixRede</code> that extends <code>WC_Payment_Gateway</code><br>- This class facilitates the integration of Rede Pix payment method within WooCommerce<br>- It provides essential functionalities for configuring and processing payments using Rede Pix, enabling seamless transactions for users<br>- The file encapsulates the logic required to handle payment settings, descriptions, icons, and other necessary configurations for the Rede Pix payment gateway within the WooCommerce ecosystem<br>- By utilizing this class, developers can easily incorporate and customize Rede Pix payment functionality in their WooCommerce stores, enhancing the overall payment experience for customers.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeAbstract.php'>LknIntegrationRedeForWoocommerceWcRedeAbstract.php</a></b></td>
					<td style='padding: 8px;'>- Define and implement payment gateway functionalities for WooCommerce integration<br>- Handle order processing, payment validation, and transaction consultation<br>- Normalize card details and ensure secure transactions<br>- Generate meta tables for order details display<br>- Streamline checkout process and enhance user experience.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRede.php'>LknIntegrationRedeForWoocommerceWcRede.php</a></b></td>
					<td style='padding: 8px;'>- Manage integration of multiple payment gateways for WooCommerce orders, ensuring seamless transactions<br>- Update orders, handle missing WooCommerce notices, and manage soft descriptors for error handling<br>- Facilitate gateway additions and version upgrades for enhanced functionality.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcMaxipagoCredit.php'>LknIntegrationRedeForWoocommerceWcMaxipagoCredit.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceWcMaxipagoCredit</code> class within the project is responsible for integrating and enabling payments with Maxipago Credit in WooCommerce<br>- It provides functionalities to configure payment settings, handle refunds, and display the necessary fields for users to make payments using Maxipago Credit<br>- This class extends an abstract class, <code>LknIntegrationRedeForWoocommerceWcRedeAbstract</code>, and encapsulates the logic specific to processing payments with Maxipago Credit within the WooCommerce environment.By utilizing this class, developers can seamlessly integrate Maxipago Credit payment options into their WooCommerce setup, offering customers a convenient and secure payment method<br>- The class abstracts the complexities of payment processing, allowing for easy configuration and management of Maxipago Credit payments within the WooCommerce ecosystem.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcMaxipagoCreditBlocks.php'>LknIntegrationRedeForWoocommerceWcMaxipagoCreditBlocks.php</a></b></td>
					<td style='padding: 8px;'>- Implement a WooCommerce payment method integration for Maxipago credit card processing<br>- Handles payment method initialization, availability checks, and script enqueuing<br>- Provides payment method data like installment options, card details, and translations<br>- Supports custom CSS and interest calculations based on settings.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceDeactivator.php'>LknIntegrationRedeForWoocommerceDeactivator.php</a></b></td>
					<td style='padding: 8px;'>Deactivate scheduled hook update_rede_orders during plugin deactivation in the LknIntegrationRedeForWoocommerce codebase.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeCredit.php'>LknIntegrationRedeForWoocommerceWcRedeCredit.php</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>LknIntegrationRedeForWoocommerceWcRedeCredit.php</code> file, located in the <code>Includes</code> directory of the project, defines a class that handles payment integration specifically for Rede Credit within a WooCommerce environment<br>- This class sets up the necessary configurations and functionalities to enable customers to pay using Rede Credit, providing a seamless payment experience within the WooCommerce platform<br>- It encapsulates the logic required to process payments, handle refunds, and display relevant information to users, enhancing the overall payment processing capabilities of the system.This file plays a crucial role in expanding the payment options available to customers using the WooCommerce platform by integrating the Rede Credit payment method, thereby enriching the user experience and increasing the flexibility of payment processing within the system architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcRedeDebitBlocks.php'>LknIntegrationRedeForWoocommerceWcRedeDebitBlocks.php</a></b></td>
					<td style='padding: 8px;'>- Implementing a custom payment method for WooCommerce Blocks, the code in LknIntegrationRedeForWoocommerceWcRedeDebitBlocks.php integrates Rede debit card payments<br>- It initializes settings, registers scripts, and provides payment method data for seamless checkout experiences<br>- This class extends WooCommerce Blocks AbstractPaymentMethodType, enhancing the projects payment processing capabilities.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/LknIntegrationRedeForWoocommerceWcEndpoint.php'>LknIntegrationRedeForWoocommerceWcEndpoint.php</a></b></td>
					<td style='padding: 8px;'>- Register various REST routes for handling different payment integrations and order management tasks based on specific conditions<br>- The class ensures seamless communication with external payment gateways and updates order statuses accordingly<br>- It also includes functionalities for clearing order logs and verifying payment transaction statuses.</td>
				</tr>
			</table>
			<!-- views Submodule -->
			<details>
				<summary><b>views</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Includes.views</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/views/LknIntegrationRedeForWoocommerceMetaTable.php'>LknIntegrationRedeForWoocommerceMetaTable.php</a></b></td>
							<td style='padding: 8px;'>- Generate a detailed HTML table displaying WooCommerce order metadata in a structured format<br>- The table includes key-value pairs for various order attributes, enhancing the presentation of order details within the WooCommerce integration.</td>
						</tr>
					</table>
					<!-- notices Submodule -->
					<details>
						<summary><b>notices</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Includes.views.notices</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/views/notices/lkn-integration-rede-for-woocommerce-notice-download.php'>lkn-integration-rede-for-woocommerce-notice-download.php</a></b></td>
									<td style='padding: 8px;'>- Provide a notice recommending the installation of a fraud prevention plugin for WooCommerce to enhance order security<br>- The notice includes a link for users to download the plugin from GitHub.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/views/notices/html-notice-woocommerce-missing.php'>html-notice-woocommerce-missing.php</a></b></td>
									<td style='padding: 8px;'>- Generate an HTML notice for missing WooCommerce integration<br>- Determine the appropriate URL based on user permissions to install the required plugin<br>- Display an error message with a link to the plugin for seamless integration.</td>
								</tr>
							</table>
						</blockquote>
					</details>
				</blockquote>
			</details>
			<!-- files Submodule -->
			<details>
				<summary><b>files</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Includes.files</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/files/linkCurrencies.json'>linkCurrencies.json</a></b></td>
							<td style='padding: 8px;'>- Generate a comprehensive list of currencies with their exchange rates based on the provided JSON data<br>- This file serves as a reference point for currency conversion within the projects architecture.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- templates Submodule -->
			<details>
				<summary><b>templates</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Includes.templates</b></code>
					<!-- pix Submodule -->
					<details>
						<summary><b>pix</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Includes.templates.pix</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/pix/pixRedePaymentPaymentFields.php'>pixRedePaymentPaymentFields.php</a></b></td>
									<td style='padding: 8px;'>- Describe how the code file enhances the checkout experience by displaying a payment option for customers using pix<br>- It retrieves settings, presents a description, and showcases the payment method logo<br>- This file contributes to the seamless integration of pix payments within the WooCommerce platform.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/pix/paymentPixQRCode.php'>paymentPixQRCode.php</a></b></td>
									<td style='padding: 8px;'>- Describe how the provided code file enhances the user experience by displaying payment instructions, facilitating QR code scanning, and enabling payment confirmation for a Pix transaction<br>- The file also showcases payment details, allows for code copying, and offers social sharing functionality.</td>
								</tr>
							</table>
						</blockquote>
					</details>
					<!-- creditCard Submodule -->
					<details>
						<summary><b>creditCard</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Includes.templates.creditCard</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/creditCard/maxipagoPaymentCreditForm.php'>maxipagoPaymentCreditForm.php</a></b></td>
									<td style='padding: 8px;'>- Project SummaryThe provided code file <code>maxipagoPaymentCreditForm.php</code> within the <code>templates/creditCard</code> directory is a crucial component of the project's architecture<br>- It plays a significant role in rendering the credit card payment form for the Maxipago payment gateway within the project's e-commerce functionality<br>- This file ensures a seamless user experience by presenting a visually appealing and functional credit card input interface tailored to the selected WordPress theme.By dynamically adjusting the styling and classes based on the active theme, this code file enhances the integration of the Maxipago payment gateway with different WordPress themes, such as Hello Elementor and OceanWP<br>- It contributes to the overall user interaction and payment processing flow within the e-commerce platform, aligning the payment form's appearance with the chosen theme for a cohesive design.In essence, <code>maxipagoPaymentCreditForm.php</code> encapsulates the logic for customizing the credit card input form based on the active WordPress theme, ensuring a consistent and optimized payment experience for users interacting with the e-commerce platform integrated with the Maxipago payment gateway.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/creditCard/redePaymentCreditForm.php'>redePaymentCreditForm.php</a></b></td>
									<td style='padding: 8px;'>- Project SummaryThe <code>redePaymentCreditForm.php</code> file, located in the <code>templates/creditCard</code> directory, is a crucial component of the project's architecture<br>- This file is responsible for rendering the credit card payment form for the Rede payment gateway within the WooCommerce environment<br>- It plays a vital role in providing users with a seamless and secure payment experience when purchasing products using a credit card on the platform.By including this file in the project structure, developers ensure that customers can conveniently and securely complete transactions using their credit cards<br>- The form's design and functionality are essential for maintaining a user-friendly interface and enhancing the overall shopping experience on the platform.In essence, the <code>redePaymentCreditForm.php</code> file encapsulates the logic necessary to present the credit card payment form, contributing significantly to the projects e-commerce functionality and ensuring smooth payment processing for customers.</td>
								</tr>
							</table>
						</blockquote>
					</details>
					<!-- debitCard Submodule -->
					<details>
						<summary><b>debitCard</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Includes.templates.debitCard</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/debitCard/maxipagoPaymentDebitForm.php'>maxipagoPaymentDebitForm.php</a></b></td>
									<td style='padding: 8px;'>- Project SummaryThe <code>maxipagoPaymentDebitForm.php</code> file in the <code>Includes/templates/debitCard</code> directory of the project is responsible for rendering the payment form for debit card transactions using the Maxipago payment gateway within the WooCommerce plugin<br>- This form provides users with the option to pay for their purchases using a debit card securely<br>- The file fetches relevant settings and descriptions from the WooCommerce Maxipago Debit settings and displays them appropriately in the payment form interface.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/debitCard/redePaymentDebitForm.php'>redePaymentDebitForm.php</a></b></td>
									<td style='padding: 8px;'>- SummaryThe <code>redePaymentDebitForm.php</code> file, located in the <code>Includes/templates/debitCard</code> directory, is a crucial component of the projects architecture<br>- This file is responsible for rendering the debit card payment form for the Rede payment gateway within the WooCommerce plugin<br>- It retrieves the necessary configuration options and displays the form elements required for customers to make payments using their debit cards<br>- This file plays a vital role in facilitating secure and seamless debit card transactions through the WooCommerce platform, enhancing the overall user experience and expanding payment options for customers.</td>
								</tr>
							</table>
						</blockquote>
					</details>
					<!-- adminCard Submodule -->
					<details>
						<summary><b>adminCard</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Includes.templates.adminCard</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Includes/templates/adminCard/adminSettingsCard.php'>adminSettingsCard.php</a></b></td>
									<td style='padding: 8px;'>- Describe the purpose and use of the <code>adminSettingsCard.php</code> file within the projects architecture<br>- This file generates a customizable settings card for the WooCommerce plugin, displaying various links, support options, and a rating feature<br>- It enhances user experience by providing easy access to documentation, support channels, and plugin reviews.</td>
								</tr>
							</table>
						</blockquote>
					</details>
				</blockquote>
			</details>
		</blockquote>
	</details>
	<!-- Admin Submodule -->
	<details>
		<summary><b>Admin</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ Admin</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/index.php'>index.php</a></b></td>
					<td style='padding: 8px;'>Enable silent operation for the Admin section.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/LknIntegrationRedeForWoocommerceAdmin.php'>LknIntegrationRedeForWoocommerceAdmin.php</a></b></td>
					<td style='padding: 8px;'>- Define and enqueue stylesheets and JavaScript for the admin area of the plugin<br>- Set properties for the plugin name and version<br>- Localize scripts with custom data for various functionalities like PRO features, manual capture, custom CSS, and more<br>- Handle different sections and gateways for specific script enqueues based on user interactions within the WooCommerce settings.</td>
				</tr>
			</table>
			<!-- js Submodule -->
			<details>
				<summary><b>js</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Admin.js</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-admin-pro-fields.js'>lkn-integration-rede-for-woocommerce-admin-pro-fields.js</a></b></td>
							<td style='padding: 8px;'>- Generate custom WooCommerce Pro fields for integration with Rede payment gateway, enhancing checkout experience<br>- Includes options for licenses, currencies, quotes, auto-capture, custom CSS, and installment configurations<br>- Facilitates seamless setup and customization for advanced payment processing within the WooCommerce platform.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-admin-clear-logs-button.js'>lkn-integration-rede-for-woocommerce-admin-clear-logs-button.js</a></b></td>
							<td style='padding: 8px;'>- Implement a feature that enhances WooCommerce admin functionality by providing a clear logs button<br>- This button allows users to easily manage and clear order records, improving the overall user experience within the WooCommerce admin interface.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-pix-settings.js'>lkn-integration-rede-for-woocommerce-pix-settings.js</a></b></td>
							<td style='padding: 8px;'>- Enhances WooCommerce integration for Rede by setting default values and adding pro version info<br>- Ensures consistent user experience by standardizing input values and displaying upgrade prompts<br>- Improves UI styling for select elements dynamically.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-endpoint.js'>lkn-integration-rede-for-woocommerce-endpoint.js</a></b></td>
							<td style='padding: 8px;'>- Create and append endpoint elements for WooCommerce integrations based on provided settings and URLs<br>- Display success or error status with appropriate icons and messages, including a link for configuration guidance.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-plugin-rate.js'>lkn-integration-rede-for-woocommerce-plugin-rate.js</a></b></td>
							<td style='padding: 8px;'>- Integrates a message in the WordPress admin footer prompting users to rate the Woo-rede plugin<br>- The message includes a link for users to leave a rating, enhancing user engagement and feedback collection<br>- The code dynamically appends the message to the admin page and updates it upon user interaction.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-admin-card.js'>lkn-integration-rede-for-woocommerce-admin-card.js</a></b></td>
							<td style='padding: 8px;'>- Integrates and displays a card for WooCommerce settings based on the admin page and plugin type<br>- Handles responsive layout adjustments for optimal user experience.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-admin.js'>lkn-integration-rede-for-woocommerce-admin.js</a></b></td>
							<td style='padding: 8px;'>- Enhances WooCommerce admin settings by displaying a notice with helpful links based on the current page<br>- Additionally, it dynamically adjusts form fields based on user interactions, improving the user experience.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/js/lkn-integration-rede-for-woocommerce-settings-layout.js'>lkn-integration-rede-for-woocommerce-settings-layout.js</a></b></td>
							<td style='padding: 8px;'>- Enhances WooCommerce settings layout by organizing elements into a more user-friendly interface<br>- Automatically hides specific fields, creates a new menu structure, and improves field descriptions for better usability<br>- Facilitates navigation and comprehension within the settings page, optimizing the user experience.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- css Submodule -->
			<details>
				<summary><b>css</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Admin.css</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/css/lkn-integration-rede-for-woocommerce-admin.css'>lkn-integration-rede-for-woocommerce-admin.css</a></b></td>
							<td style='padding: 8px;'>- Define the admin-specific styling for WooCommerce integration<br>- Customize tooltips, settings layout, and menu appearance<br>- Enhance user experience with responsive design adjustments<br>- Optimize visual elements for various screen sizes<br>- Maintain consistency and clarity in design across devices.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- partials Submodule -->
			<details>
				<summary><b>partials</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Admin.partials</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Admin/partials/lkn-integration-rede-for-woocommerce-admin-display.php'>lkn-integration-rede-for-woocommerce-admin-display.php</a></b></td>
							<td style='padding: 8px;'>- Render the admin view for the WooCommerce plugin integration with Rede<br>- This file structures the visual elements for the plugins backend functionality<br>- Its crucial for presenting the necessary information and controls to manage the integration effectively.</td>
						</tr>
					</table>
				</blockquote>
			</details>
		</blockquote>
	</details>
	<!-- languages Submodule -->
	<details>
		<summary><b>languages</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ languages</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/languages/integration-rede-for-woocommerce-pt_BR.po'>integration-rede-for-woocommerce-pt_BR.po</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>integration-rede-for-woocommerce-pt_BR.po</code> file in the <code>languages</code> directory of the project serves the purpose of providing language localization support for the Integration Rede plugin tailored for WooCommerce in Brazilian Portuguese (<code>pt_BR</code>)<br>- This file contains translations for various text strings used in the plugin interface, ensuring a seamless user experience for Portuguese-speaking users<br>- The translations include essential elements like payment method descriptions, ensuring clarity and usability for the target audience.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/languages/lkn-integration-rede-for-woocommerce.pot'>lkn-integration-rede-for-woocommerce.pot</a></b></td>
					<td style='padding: 8px;'>- SummaryThe <code>lkn-integration-rede-for-woocommerce.pot</code> file in the <code>languages</code> directory of the project serves as a blank WordPress Pot template<br>- It contains essential metadata such as project version, creation date, language information, and text encoding details<br>- This file acts as a foundation for language translations and localization within the WooCommerce integration with Rede, providing a structured starting point for multilingual support in the plugin.By utilizing this template, developers and translators can efficiently create language-specific versions of the plugin, ensuring a seamless user experience for WooCommerce users across different regions and languages<br>- This file plays a crucial role in the internationalization of the project, enabling broader accessibility and usability for a diverse user base.</td>
				</tr>
			</table>
		</blockquote>
	</details>
	<!-- Public Submodule -->
	<details>
		<summary><b>Public</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ Public</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/LknIntegrationRedeForWoocommercePublic.php'>LknIntegrationRedeForWoocommercePublic.php</a></b></td>
					<td style='padding: 8px;'>- Define and manage public-facing stylesheets and JavaScript for the plugin<br>- Enqueue these assets using specific hooks to ensure proper functionality<br>- This class establishes the necessary connections between defined hooks and corresponding functions within the codebase.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/index.php'>index.php</a></b></td>
					<td style='padding: 8px;'>- Initiates the application by serving as the entry point<br>- This file plays a crucial role in the project structure, setting the foundation for the entire codebase architecture.</td>
				</tr>
			</table>
			<!-- js Submodule -->
			<details>
				<summary><b>js</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Public.js</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/jquery.card.js'>jquery.card.js</a></b></td>
							<td style='padding: 8px;'>- The <code>jquery.card.js</code> file in the project serves as a crucial component for handling card-related functionalities<br>- It encapsulates the logic for managing and manipulating card elements within the project's architecture<br>- This file plays a vital role in enhancing user interactions related to card elements, contributing to a seamless and intuitive user experience<br>- By leveraging the capabilities provided by <code>jquery.card.js</code>, developers can efficiently integrate and manage card functionalities within the broader project structure.By abstracting the complexities of card manipulation into a concise and reusable module, this file simplifies the implementation of card-related features across the project<br>- Its presence streamlines the development process and empowers developers to focus on creating engaging user interfaces without getting bogged down by intricate card-handling details.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/fixInfiniteLoading.js'>fixInfiniteLoading.js</a></b></td>
							<td style='padding: 8px;'>Improve user experience by automatically hiding loading indicators after AJAX requests complete.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/lknIntegrationRedeForWoocommercePublic.js'>lknIntegrationRedeForWoocommercePublic.js</a></b></td>
							<td style='padding: 8px;'>- Enhances public-facing JavaScript functionality by encapsulating code for DOM manipulation and event handling<br>- Promotes best practices for maintaining clean and efficient code within the WordPress ecosystem.</td>
						</tr>
					</table>
					<!-- pix Submodule -->
					<details>
						<summary><b>pix</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Public.js.pix</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/pix/LknIntegrationRedeForWoocommercePixRede.js'>LknIntegrationRedeForWoocommercePixRede.js</a></b></td>
									<td style='padding: 8px;'>- Create and register a payment method block for integrating Rede Pix with WooCommerce<br>- Display Rede Pix logo and description, allowing payments and supporting specified features.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/pix/LknIntegrationRedeForWoocommercePix.js'>LknIntegrationRedeForWoocommercePix.js</a></b></td>
									<td style='padding: 8px;'>- Implement a Pix integration for WooCommerce donations, handling payment verification and user interactions<br>- The code manages payment status checks, disables buttons upon completion, and enables sharing and copying functionalities<br>- It ensures a seamless payment experience for users, enhancing the overall donation process within the WooCommerce platform.</td>
								</tr>
							</table>
						</blockquote>
					</details>
					<!-- creditCard Submodule -->
					<details>
						<summary><b>creditCard</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Public.js.creditCard</b></code>
							<!-- maxipago Submodule -->
							<details>
								<summary><b>maxipago</b></summary>
								<blockquote>
									<div class='directory-path' style='padding: 8px 0; color: #666;'>
										<code><b>⦿ Public.js.creditCard.maxipago</b></code>
									<table style='width: 100%; border-collapse: collapse;'>
									<thead>
										<tr style='background-color: #f8f9fa;'>
											<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
											<th style='text-align: left; padding: 8px;'>Summary</th>
										</tr>
									</thead>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js'>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js</a></b></td>
											<td style='padding: 8px;'>- SummaryThe <code>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js</code> file in the projects architecture is designed to facilitate seamless integration of Maxipago payment gateway functionality specifically tailored for WooCommerce checkout processes<br>- This code file plays a crucial role in enabling secure and efficient payment processing within the WooCommerce platform by leveraging Maxipago services.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js.LICENSE.txt'>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js.LICENSE.txt</a></b></td>
											<td style='padding: 8px;'>- Integrate Maxipago for Woocommerce Checkout<br>- This file includes necessary dependencies for seamless payment processing<br>- It ensures smooth communication between the Woocommerce platform and Maxipago payment gateway.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.jsx'>lknIntegrationMaxipagoForWoocommerceCheckout.jsx</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for Maxipago credit card integration in a WooCommerce checkout<br>- The component handles credit card details, validations, and payment setup<br>- It utilizes settings from the global environment and supports multiple installments<br>- The component ensures all fields are filled before processing the payment, enhancing the user experience during checkout.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/maxipago/wooMaxipagoCredit.js'>wooMaxipagoCredit.js</a></b></td>
											<td style='padding: 8px;'>- Render Maxipago credit card form on various checkout events, ensuring proper formatting of CPF input<br>- Dynamically update payment form elements based on selected method<br>- Maintain card data post-checkout updates for a seamless user experience<br>- Handle CPF/CNPJ field interactions and hide unnecessary payment box details<br>- Refresh payment fields via AJAX for a smooth checkout process.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.js'>lknIntegrationMaxipagoForWoocommerceCheckout.js</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for Maxipago credit card integration in a WooCommerce checkout<br>- The component handles credit card details, installment options, and form validations<br>- It dynamically updates options based on user interactions and backend data<br>- This code enhances the checkout experience by providing a seamless and secure payment process.</td>
										</tr>
									</table>
								</blockquote>
							</details>
							<!-- rede Submodule -->
							<details>
								<summary><b>rede</b></summary>
								<blockquote>
									<div class='directory-path' style='padding: 8px 0; color: #666;'>
										<code><b>⦿ Public.js.creditCard.rede</b></code>
									<table style='width: 100%; border-collapse: collapse;'>
									<thead>
										<tr style='background-color: #f8f9fa;'>
											<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
											<th style='text-align: left; padding: 8px;'>Summary</th>
										</tr>
									</thead>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/rede/wooRedeCredit.js'>wooRedeCredit.js</a></b></td>
											<td style='padding: 8px;'>- Render credit card information dynamically on the checkout page based on selected payment method, providing a seamless user experience<br>- Handles card input validation, placeholders, and animations<br>- Updates payment fields dynamically without page reloads, ensuring a smooth checkout process.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckoutCompiled.js'>lknIntegrationRedeForWoocommerceCheckoutCompiled.js</a></b></td>
											<td style='padding: 8px;'>- The <code>lknIntegrationRedeForWoocommerceCheckoutCompiled.js</code> file in the projects architecture plays a crucial role in facilitating seamless integration of Rede payment gateway functionality into WooCommerce checkout processes<br>- This code file serves as a key component in enabling secure and efficient payment processing for users utilizing the WooCommerce platform.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckout.jsx'>lknIntegrationRedeForWoocommerceCheckout.jsx</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for integrating Rede credit card payments into a WooCommerce checkout<br>- Handles credit card details input, validation, and submission<br>- Renders card fields, including number, holder name, expiry, and CVC, with dynamic formatting<br>- Supports selecting installment options based on settings<br>- Enables seamless payment processing within the WooCommerce environment.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckoutCompiled.js.LICENSE.txt'>lknIntegrationRedeForWoocommerceCheckoutCompiled.js.LICENSE.txt</a></b></td>
											<td style='padding: 8px;'>Integrate object-assign, regenerator-runtime, and React v16.14.0 for WooCommerce checkout.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckout.js'>lknIntegrationRedeForWoocommerceCheckout.js</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for integrating Rede credit card payments into WooCommerce checkout<br>- The component handles credit card details, installment options, and form validation<br>- It dynamically fetches data, updates options, and ensures a smooth payment setup process<br>- This code enhances the user experience by simplifying credit card payments within the WooCommerce platform.</td>
										</tr>
									</table>
								</blockquote>
							</details>
						</blockquote>
					</details>
					<!-- debitCard Submodule -->
					<details>
						<summary><b>debitCard</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Public.js.debitCard</b></code>
							<!-- maxipago Submodule -->
							<details>
								<summary><b>maxipago</b></summary>
								<blockquote>
									<div class='directory-path' style='padding: 8px 0; color: #666;'>
										<code><b>⦿ Public.js.debitCard.maxipago</b></code>
									<table style='width: 100%; border-collapse: collapse;'>
									<thead>
										<tr style='background-color: #f8f9fa;'>
											<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
											<th style='text-align: left; padding: 8px;'>Summary</th>
										</tr>
									</thead>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js'>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js</a></b></td>
											<td style='padding: 8px;'>- The code file <code>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js</code> within the project structure plays a crucial role in integrating Maxipago payment functionality into the WooCommerce checkout process<br>- It facilitates seamless transactions by enabling communication between the WooCommerce platform and Maxipago payment gateway<br>- This integration enhances the user experience by providing a secure and efficient payment method for customers using the WooCommerce e-commerce platform.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/maxipago/wooMaxipagoDebit.js'>wooMaxipagoDebit.js</a></b></td>
											<td style='padding: 8px;'>- Render Maxipago debit card details dynamically on WooCommerce checkout, enhancing user experience<br>- Automatically formats CPF input, displays animated card, and maintains data post-checkout updates<br>- Improves payment flow efficiency and provides a seamless checkout process.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js.LICENSE.txt'>lknIntegrationMaxipagoForWoocommerceCheckoutCompiled.js.LICENSE.txt</a></b></td>
											<td style='padding: 8px;'>Describe the purpose and use of the provided code file within the projects architecture.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.jsx'>lknIntegrationMaxipagoForWoocommerceCheckout.jsx</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for Maxipago debit card integration in a WooCommerce checkout<br>- The component handles card details input, formatting, and validation, ensuring all fields are filled before processing the payment<br>- It integrates with Maxipago API for secure transactions, enhancing the checkout experience for users.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.js'>lknIntegrationMaxipagoForWoocommerceCheckout.js</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for Maxipago debit card integration in a WooCommerce checkout<br>- Handles card details input, formatting, and validation<br>- Registers the Maxipago debit gateway for payments, supporting various features<br>- Enables seamless payment processing within the WooCommerce ecosystem.</td>
										</tr>
									</table>
								</blockquote>
							</details>
							<!-- rede Submodule -->
							<details>
								<summary><b>rede</b></summary>
								<blockquote>
									<div class='directory-path' style='padding: 8px 0; color: #666;'>
										<code><b>⦿ Public.js.debitCard.rede</b></code>
									<table style='width: 100%; border-collapse: collapse;'>
									<thead>
										<tr style='background-color: #f8f9fa;'>
											<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
											<th style='text-align: left; padding: 8px;'>Summary</th>
										</tr>
									</thead>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckoutCompiled.js'>lknIntegrationRedeForWoocommerceCheckoutCompiled.js</a></b></td>
											<td style='padding: 8px;'>- SummaryThe <code>lknIntegrationRedeForWoocommerceCheckoutCompiled.js</code> file in the <code>Public/js/debitCard/rede/</code> directory of the project plays a crucial role in integrating Rede payment gateway functionality into WooCommerce checkout processes<br>- This file facilitates seamless transactions and enhances the user experience by enabling secure payment processing through the Rede platform within the WooCommerce ecosystem.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckout.jsx'>lknIntegrationRedeForWoocommerceCheckout.jsx</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for integrating Rede debit card payments into WooCommerce checkout<br>- The component handles card details input, validation, and submission<br>- It leverages global settings for configuration and interacts with the WooCommerce Blocks API for seamless integration<br>- The component ensures a smooth payment experience for users, enhancing the checkout process.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckoutCompiled.js.LICENSE.txt'>lknIntegrationRedeForWoocommerceCheckoutCompiled.js.LICENSE.txt</a></b></td>
											<td style='padding: 8px;'>Integrate object-assign, regenerator-runtime, and React v16.14.0 for WooCommerce checkout.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckout.js'>lknIntegrationRedeForWoocommerceCheckout.js</a></b></td>
											<td style='padding: 8px;'>- Implement a React component for integrating Rede debit card payments into WooCommerce checkout<br>- The component handles card details input, validation, and submission<br>- It leverages React Credit Cards for visual representation and interacts with WooCommerce settings for configuration<br>- The code ensures a seamless payment experience for users during checkout.</td>
										</tr>
										<tr style='border-bottom: 1px solid #eee;'>
											<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/js/debitCard/rede/wooRedeDebit.js'>wooRedeDebit.js</a></b></td>
											<td style='padding: 8px;'>- Render Rede debit card form dynamically based on selected payment method, enhancing user checkout experience<br>- Handles card creation and display, ensuring smooth functionality even after checkout updates<br>- Improves user interaction by providing a seamless payment process.</td>
										</tr>
									</table>
								</blockquote>
							</details>
						</blockquote>
					</details>
				</blockquote>
			</details>
			<!-- css Submodule -->
			<details>
				<summary><b>css</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Public.css</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css'>lknIntegrationRedeForWoocommerceSelectStyle.css</a></b></td>
							<td style='padding: 8px;'>- Enhances WooCommerce select styles for seamless integration with Rede payment gateway<br>- Adjusts select elements width, padding, and font size for a consistent user experience<br>- Improves layout by aligning labels and customizing select box appearance.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/card.css'>card.css</a></b></td>
							<td style='padding: 8px;'>- SummaryThe <code>card.css</code> file in the <code>Public/css</code> directory is responsible for styling a specific type of card component within the project<br>- It defines the visual appearance of a card element when it is identified and viewed in a Safari browser<br>- The styles specified in this file use gradients to create a visually appealing effect on the front and back faces of the card.This CSS file plays a crucial role in enhancing the user interface of the project by ensuring that the card component is visually engaging and consistent with the overall design language<br>- It contributes to the aesthetic appeal and user experience of the application when displaying these specific card elements in Safari, making them stand out and providing a polished look.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/lknIntegrationRedeForWoocommercePublic.css'>lknIntegrationRedeForWoocommercePublic.css</a></b></td>
							<td style='padding: 8px;'>Hide payment method images for specific integrations in the WooCommerce checkout page.</td>
						</tr>
					</table>
					<!-- maxipago Submodule -->
					<details>
						<summary><b>maxipago</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Public.css.maxipago</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/maxipago/styleMaxipagoDebit.css'>styleMaxipagoDebit.css</a></b></td>
									<td style='padding: 8px;'>- Enhance payment form visuals by incorporating Maxipago branding elements and credit card icons<br>- Improve user experience with SVG icons in inputs and adjust card animation for a polished look<br>- Achieve a cohesive design language throughout the payment process.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/maxipago/styleMaxipagoCredit.css'>styleMaxipagoCredit.css</a></b></td>
									<td style='padding: 8px;'>- Enhances the visual presentation of credit card payment forms by incorporating Maxipago branding elements and card icons<br>- Improves user experience by adding SVG icons to inputs and adjusting card animation for a polished look<br>- The CSS file <code>styleMaxipagoCredit.css</code> in the <code>Public/css/maxipago</code> directory plays a crucial role in styling payment forms within the project architecture.</td>
								</tr>
							</table>
						</blockquote>
					</details>
					<!-- rede Submodule -->
					<details>
						<summary><b>rede</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Public.css.rede</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/rede/styleRedeDebit.css'>styleRedeDebit.css</a></b></td>
									<td style='padding: 8px;'>- Enhance payment form visuals by incorporating logos and icons for various payment methods<br>- Improve user experience by adding SVG icons to inputs and adjusting card animation for a polished look<br>- Ensure seamless integration of payment options with a focus on design aesthetics and functionality.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/rede/LknIntegrationRedeForWoocommercePaymentFields.css'>LknIntegrationRedeForWoocommercePaymentFields.css</a></b></td>
									<td style='padding: 8px;'>- Enhance WooCommerce payment fields styling for Rede integration<br>- Display payment fields with centered alignment and specific dimensions<br>- Adjust margins and padding for a seamless checkout experience.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/rede/LknIntegrationRedeForWoocommercePix.css'>LknIntegrationRedeForWoocommercePix.css</a></b></td>
									<td style='padding: 8px;'>- Define the layout and styling for integrating Pix payment method in WooCommerce<br>- Establishes a responsive design for a seamless user experience across devices<br>- Ensures consistent alignment, spacing, and visual appeal<br>- Implements interactive elements like buttons and input fields with appropriate hover effects<br>- Enhances readability with clear typography and color contrast.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/rede/styleRedeCredit.css'>styleRedeCredit.css</a></b></td>
									<td style='padding: 8px;'>- Enhances the visual representation of credit card payment forms by incorporating logos and icons for various credit card brands<br>- Improves user experience by providing recognizable visual cues for different card types<br>- Additionally, refines the animation and styling of credit card input fields for a more polished look and feel.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/css/rede/LknIntegrationRedeForWoocommercePixDivi.css'>LknIntegrationRedeForWoocommercePixDivi.css</a></b></td>
									<td style='padding: 8px;'>- Enhance spacing and styling for WooCommerce and Pix integration elements<br>- Adjust margins and padding for improved layout and readability.</td>
								</tr>
							</table>
						</blockquote>
					</details>
				</blockquote>
			</details>
			<!-- partials Submodule -->
			<details>
				<summary><b>partials</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Public.partials</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/Public/partials/lkn-integration-rede-for-woocommerce-public-display.php'>lkn-integration-rede-for-woocommerce-public-display.php</a></b></td>
							<td style='padding: 8px;'>- Display public-facing view for the plugin, focusing on HTML with minimal PHP<br>- Located in Public/partials/lkn-integration-rede-for-woocommerce-public-display.php within the LknIntegrationRedeForWoocommerce project structure<br>- Aimed at showcasing plugin aspects to users.</td>
						</tr>
					</table>
				</blockquote>
			</details>
		</blockquote>
	</details>
	<!-- .github Submodule -->
	<details>
		<summary><b>.github</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ .github</b></code>
			<!-- workflows Submodule -->
			<details>
				<summary><b>workflows</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ .github.workflows</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/.github/workflows/main.yml'>main.yml</a></b></td>
							<td style='padding: 8px;'>- Generate new releases for the plugin by running various checks, preparing the plugin folder, archiving it as a.zip, moving the.zip to a custom location, updating the version tag, and creating a new release<br>- This workflow automates the release process for the plugin, ensuring a streamlined deployment on GitHub.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/master/.github/workflows/wordpressRelease.yml'>wordpressRelease.yml</a></b></td>
							<td style='padding: 8px;'>- Deploy Plugin to WordPress.org workflow automates the deployment of the woo-rede plugin version 4.1.0<br>- It ensures the plugin is prepared, packaged, and deployed to the WordPress.org repository using specified PHP version 7.4<br>- The workflow handles tasks like composer installation, folder preparation, and SVN deployment, streamlining the release process.</td>
						</tr>
					</table>
				</blockquote>
			</details>
		</blockquote>
	</details>
</details>

---

## Getting Started

### Prerequisites

This project requires the following dependencies:

- **Programming Language:** PHP
- **Package Manager:** Composer, Npm

### Installation

Build integration-rede-for-woocommerce from the source and intsall dependencies:

1. **Clone the repository:**

    ```sh
    ❯ git clone https://github.com/LinkNacional/integration-rede-for-woocommerce
    ```

2. **Navigate to the project directory:**

    ```sh
    ❯ cd integration-rede-for-woocommerce
    ```

3. **Install the dependencies:**

<!-- SHIELDS BADGE CURRENTLY DISABLED -->
	<!-- [![composer][composer-shield]][composer-link] -->
	<!-- REFERENCE LINKS -->
	<!-- [composer-shield]: https://img.shields.io/badge/PHP-777BB4.svg?style={badge_style}&logo=php&logoColor=white -->
	<!-- [composer-link]: https://www.php.net/ -->

	**Using [composer](https://www.php.net/):**

	```sh
	❯ composer install
	```
<!-- SHIELDS BADGE CURRENTLY DISABLED -->
	<!-- [![npm][npm-shield]][npm-link] -->
	<!-- REFERENCE LINKS -->
	<!-- [npm-shield]: None -->
	<!-- [npm-link]: None -->

	**Using [npm](None):**

	```sh
	❯ echo 'INSERT-INSTALL-COMMAND-HERE'
	```

### Usage

Run the project with:

**Using [composer](https://www.php.net/):**
```sh
php {entrypoint}
```
**Using [npm](None):**
```sh
echo 'INSERT-RUN-COMMAND-HERE'
```

### Testing

Integration-rede-for-woocommerce uses the {__test_framework__} test framework. Run the test suite with:

**Using [composer](https://www.php.net/):**
```sh
vendor/bin/phpunit
```
**Using [npm](None):**
```sh
echo 'INSERT-TEST-COMMAND-HERE'
```

---

## Roadmap

- [X] **`Task 1`**: <strike>Implement feature one.</strike>
- [ ] **`Task 2`**: Implement feature two.
- [ ] **`Task 3`**: Implement feature three.

---

## Contributing

- **💬 [Join the Discussions](https://github.com/LinkNacional/integration-rede-for-woocommerce/discussions)**: Share your insights, provide feedback, or ask questions.
- **🐛 [Report Issues](https://github.com/LinkNacional/integration-rede-for-woocommerce/issues)**: Submit bugs found or log feature requests for the `integration-rede-for-woocommerce` project.
- **💡 [Submit Pull Requests](https://github.com/LinkNacional/integration-rede-for-woocommerce/blob/main/CONTRIBUTING.md)**: Review open PRs, and submit your own PRs.

<details closed>
<summary>Contributing Guidelines</summary>

1. **Fork the Repository**: Start by forking the project repository to your github account.
2. **Clone Locally**: Clone the forked repository to your local machine using a git client.
   ```sh
   git clone https://github.com/LinkNacional/integration-rede-for-woocommerce
   ```
3. **Create a New Branch**: Always work on a new branch, giving it a descriptive name.
   ```sh
   git checkout -b new-feature-x
   ```
4. **Make Your Changes**: Develop and test your changes locally.
5. **Commit Your Changes**: Commit with a clear message describing your updates.
   ```sh
   git commit -m 'Implemented new feature x.'
   ```
6. **Push to github**: Push the changes to your forked repository.
   ```sh
   git push origin new-feature-x
   ```
7. **Submit a Pull Request**: Create a PR against the original project repository. Clearly describe the changes and their motivations.
8. **Review**: Once your PR is reviewed and approved, it will be merged into the main branch. Congratulations on your contribution!
</details>

<details closed>
<summary>Contributor Graph</summary>
<br>
<p align="left">
   <a href="https://github.com{/LinkNacional/integration-rede-for-woocommerce/}graphs/contributors">
      <img src="https://contrib.rocks/image?repo=LinkNacional/integration-rede-for-woocommerce">
   </a>
</p>
</details>

---

## License

Integration-rede-for-woocommerce is protected under the [LICENSE](https://choosealicense.com/licenses) License. For more details, refer to the [LICENSE](https://choosealicense.com/licenses/) file.

---

## Acknowledgments

- Credit `contributors`, `inspiration`, `references`, etc.

<div align="right">

[![][back-to-top]](#top)

</div>


[back-to-top]: https://img.shields.io/badge/-BACK_TO_TOP-151515?style=flat-square


---
