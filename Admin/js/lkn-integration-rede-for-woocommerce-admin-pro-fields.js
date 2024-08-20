function lknIntegrationRedeForWoocommerceProFields(page) {
  return (`
    <div>
      <h3 class="wc-settings-sub-title" id="woocommerce_rede_credit_PRO">${lknPhpProFieldsVariables.proSettings}</h3>
      <hr>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="woocommerce_rede_credit_license"><span class="lowOpacity">${lknPhpProFieldsVariables.license}</span> 
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                </span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext">
                  ${lknPhpProFieldsVariables.licenseDescription}
                </span>
              </label>
              <a class="lknIntegrationRedeForWoocommerceBecomePRO" href="https://www.linknacional.com.br/wordpress/woocommerce/" target="_blank">${lknPhpProFieldsVariables.becomePRO}</a>
            </th>
            <td class="forminp">
              <fieldset>
                <legend class="screen-reader-text"><span>Licença</span></legend>
                <input disabled class="input-text regular-input " type="password" name="woocommerce_rede_credit_license" id="woocommerce_rede_credit_license">
              </fieldset>
            </td>
          </tr>
          ${(page === 'rede_credit' || page === 'maxipago_credit') ? `
            <tr valign="top">
              <th scope="row" class="titledesc">
                <label for="woocommerce_rede_credit_auto_capture"><span class="lowOpacity">${lknPhpProFieldsVariables.autoCapture}</span>
                  <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                  <span class="lknIntegrationRedeForWoocommerceTooltiptext">
                    ${lknPhpProFieldsVariables.autoCaptureDescription}
                  </span>
                </label>
                <a class="lknIntegrationRedeForWoocommerceBecomePRO" href="https://www.linknacional.com.br/wordpress/woocommerce/" target="_blank">${lknPhpProFieldsVariables.becomePRO}</a>
              </th>
              <td class="forminp">
                <fieldset>
                  <label for="woocommerce_rede_credit_auto_capture">
                  <input disabled class="" type="checkbox" name="woocommerce_rede_credit_auto_capture" id="woocommerce_rede_credit_auto_capture" value="1" checked="checked"><span class="lowOpacity">${lknPhpProFieldsVariables.autoCaptureLabel}</span></label><br>
                </fieldset>
              </td>
            </tr>` : ''
          }
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="woocommerce_rede_credit_custom_css_short_code"><span class="lowOpacity">${lknPhpProFieldsVariables.customCssShortcode}</span>            
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext">
                  ${lknPhpProFieldsVariables.customCssShortcodeDescription}
                </span>
              </label>
              <a class="lknIntegrationRedeForWoocommerceBecomePRO" href="https://www.linknacional.com.br/wordpress/woocommerce/" target="_blank">${lknPhpProFieldsVariables.becomePRO}</a>
            </th>
            <td class="forminp">
              <fieldset>
                <textarea disabled rows="3" cols="20" class="input-text wide-input " type="textarea" name="woocommerce_rede_credit_custom_css_short_code" id="woocommerce_rede_credit_custom_css_short_code"></textarea>
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="woocommerce_rede_credit_custom_css_block_editor"><span class="lowOpacity">${lknPhpProFieldsVariables.customCssBlockEditor}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext">
                  ${lknPhpProFieldsVariables.customCssBlockEditorDescription}
                </span>
              </label>
              <a class="lknIntegrationRedeForWoocommerceBecomePRO" href="https://www.linknacional.com.br/wordpress/woocommerce/" target="_blank">${lknPhpProFieldsVariables.becomePRO}</a>
            </th>
            <td class="forminp">
              <fieldset>
                <textarea disabled rows="3" cols="20" class="input-text wide-input " type="textarea" name="woocommerce_rede_credit_custom_css_block_editor" id="woocommerce_rede_credit_custom_css_block_editor"></textarea>
              </fieldset>
            </td>
          </tr>
          ${(page === 'rede_credit' || page === 'maxipago_credit') ? `          
            <tr valign="top">
              <th scope="row" class="titledesc">
                <label class="lowOpacity" for="woocommerce_rede_credit_installment_interest">${lknPhpProFieldsVariables.interestOnInstallments}</label>
                <a class="lknIntegrationRedeForWoocommerceBecomePRO" href="https://www.linknacional.com.br/wordpress/woocommerce/" target="_blank">${lknPhpProFieldsVariables.becomePRO}</a>
              </th>
              <td class="forminp">
                <fieldset>
                  <label for="woocommerce_rede_credit_installment_interest">
                  <input disabled class="" type="checkbox" name="woocommerce_rede_credit_installment_interest" id="woocommerce_rede_credit_installment_interest" value="1"> <span class="lowOpacity">${lknPhpProFieldsVariables.interestOnInstallments}</span></label><br>
                  <p class="description lowOpacity">
                    ${lknPhpProFieldsVariables.interestOnInstallmentsDescription}
                  </p>
                </fieldset>
              </td>
            </tr>` : ''
          }
        </tbody>
      </table>
    </div>
  `)
}