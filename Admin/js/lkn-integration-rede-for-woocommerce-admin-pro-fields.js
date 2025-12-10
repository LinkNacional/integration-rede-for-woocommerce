function lknIntegrationRedeForWoocommerceProFields(page) {
  return (`
    <div>
      <h3 class="wc-settings-sub-title" id="woocommerce_rede_credit_PRO">${lknPhpProFieldsVariables.proSettings}</h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="woocommerce_rede_credit_license_example_field" class="lowOpacity"><span>${lknPhpProFieldsVariables.license}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                </span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.licenseDescTip}
                </span>
              </label>
            </th>
            <td class="forminp">
              <fieldset class="lowOpacity">
                <legend class="screen-reader-text"><span>Licença</span></legend>
                <input disabled class="input-text regular-input " type="password" name="woocommerce_rede_credit_license_example_field" id="woocommerce_rede_credit_license_example_field"
                data-title-description="${lknPhpProFieldsVariables.licenseDataDescription}" lkn-is-pro="true">
                <p class="description">${lknPhpProFieldsVariables.licenseDescription}</p>
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label class="lowOpacity" for="woocommerce_rede_credit_currency_example_field"><span>${lknPhpProFieldsVariables.currency}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                </span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.currencyDescTip}
                </span>
              </label>
            </th>
            <td class="forminp">
              <fieldset class="lowOpacity">
                <legend class="screen-reader-text"><span>Licença</span></legend>
                <input disabled class="input-text regular-input " type="password" name="woocommerce_rede_credit_currency_example_field" id="woocommerce_rede_credit_currency_example_field"
                lkn-is-pro="true">
                <p class="description">${lknPhpProFieldsVariables.currencyDescription}</p>
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label class="lowOpacity" for="woocommerce_rede_credit_quote_example_field"><span>${lknPhpProFieldsVariables.currencyQuote}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                </span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.currencyQuoteDescTip}
                </span>
              </label>
            </th>
            <td class="forminp">
              <fieldset class="lowOpacity">
                <input style="display:none;" id="woocommerce_rede_credit_quote_example_field"
                data-title-description="${lknPhpProFieldsVariables.quoteDataDescription}" lkn-is-pro="true"/>
                <legend class="screen-reader-text"><span>Licença</span></legend>
                <p><a onclick="return false;" class="lowOpacity" style="cursor: default; pointer-events: none;" href="#" target="_blank">View Currencies and Quotes</a></p>
              </fieldset>
            </td>
          </tr>
          ${(page === 'rede_credit' || page === 'rede_debit' || page === 'maxipago_credit') ? `
            <tr valign="top">
              <th scope="row" class="titledesc">
                <label class="lowOpacity" for="woocommerce_rede_credit_auto_capture_example_field"><span>${lknPhpProFieldsVariables.autoCapture}</span>
                  <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                  <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                    ${lknPhpProFieldsVariables.autoCaptureDescTip}
                  </span>
                </label>
              </th>
              <td class="forminp">
                <fieldset class="lowOpacity">
                  <label class="lowOpacity" for="woocommerce_rede_credit_auto_capture_example_field">
                  <input disabled class="" type="checkbox" name="woocommerce_rede_credit_auto_capture_example_field" id="woocommerce_rede_credit_auto_capture_example_field" value="1" checked="checked"
                  data-title-description="${lknPhpProFieldsVariables.autoCaptureDataDescription}" lkn-is-pro="true"
                  ><span>${page === 'rede_debit' ? lknPhpProFieldsVariables.autoCaptureDebitLabel : lknPhpProFieldsVariables.autoCaptureLabel}</span></label><br>
                  <p class="description">${lknPhpProFieldsVariables.autoCaptureDescription}</p>
                </fieldset>
              </td>
            </tr>` : ''
    }
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label class="lowOpacity" for="woocommerce_rede_credit_custom_css_short_code_example_field"><span>${lknPhpProFieldsVariables.customCssShortcode}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.customCssShortcodeDescTip}
                </span>
              </label>
            </th>
            <td class="forminp">
              <fieldset class="lowOpacity">
                <textarea disabled rows="3" cols="20" class="input-text wide-input " type="textarea" name="woocommerce_rede_credit_custom_css_short_code_example_field" id="woocommerce_rede_credit_custom_css_short_code_example_field"
                data-title-description="${lknPhpProFieldsVariables.cssShortcodeDataDescription}" lkn-is-pro="true"
                ></textarea>
                <p class="description">${lknPhpProFieldsVariables.customCssShortcodeDescription}</p> 
              </fieldset>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label class="lowOpacity" for="woocommerce_rede_credit_custom_css_block_editor_example_field"><span>${lknPhpProFieldsVariables.customCssBlockEditor}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.customCssBlockEditorDescTip}
                </span>
              </label>
            </th>
            <td class="forminp">
              <fieldset class="lowOpacity">
                <textarea disabled rows="3" cols="20" class="input-text wide-input " type="textarea" name="woocommerce_rede_credit_custom_css_block_editor_example_field" id="woocommerce_rede_credit_custom_css_block_editor_example_field"
                data-title-description="${lknPhpProFieldsVariables.cssBlockEditorDataDescription}" lkn-is-pro="true"></textarea>
                <p class="description">${lknPhpProFieldsVariables.customCssBlockEditorDescription}</p>
              </fieldset>
            </td>
          </tr>
          ${(page === 'rede_credit' || page === 'rede_debit' || page === 'maxipago_credit') ? `
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label class="lowOpacity" for="woocommerce_rede_credit_installment_example_field"><span>Configurações de Parcelamento</span>
                      <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0"></span>
                      <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                        Select the option interest or discount. Save to continue configuration.
                      </span>
                    </label>
                  </th>
                <td class="forminp">
                    <fieldset class="lowOpacity">
                        <legend class="screen-reader-text"><span>Configurações de Parcelamento</span></legend>
                        <input disabled class="input-text regular-input " value="Juros" type="text" name="woocommerce_rede_installment_example_field" id="woocommerce_rede_credit_installment_example_field"
                        data-title-description="Defines whether the installment will apply interest or offer a discount. Save to load more settings." lkn-is-pro="true"
                        >
                        <p class="description">Allows the user to select discount or interest on credit card installments.</p>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label class="lowOpacity" for="woocommerce_rede_credit_min_installment_example_field"><span>Parcela minima para sem juros</span>
                      <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                      </span>
                      <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                        Set the minimum value of each installment for the sale to be considered interest-free. If the total purchase amount is greater than the defined limit for interest-free installments, but less than this minimum value, interest will be applied automatically.
                      </span>
                    </label>
                  </th>
                <td class="forminp">
                    <fieldset class="lowOpacity">
                        <legend class="screen-reader-text"><span>Parcela minima para sem juros</span></legend>
                        <input value disabled class="input-text regular-input " type="password" name="woocommerce_rede_credit_min_installment_example_field" id="woocommerce_rede_credit_min_installment_example_field"
                        data-title-description="Defines the lowest possible value for each installment." lkn-is-pro="true">
                        <p class="description">Sets the minimum accepted installment value.</p>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
              <th scope="row" class="titledesc">
              <label class="lowOpacity" for="woocommerce_rede_credit_installment_interest_example_field"><span>${lknPhpProFieldsVariables.interestOnInstallments}</span>
                <span class="woocommerce-help-tip lowOpacity" id="lknIntegrationRedeForWoocommerceTooltipSpan" tabindex="0">
                </span>
                <span class="lknIntegrationRedeForWoocommerceTooltiptext lowOpacity">
                  ${lknPhpProFieldsVariables.interestOnInstallmentsDescTip}
                </span>
              </label>
              </th>
              <td class="forminp">
                <fieldset class="lowOpacity">
                  <label class="lowOpacity" for="woocommerce_rede_credit_installment_interest_example_field">
                  <input disabled class="" type="checkbox" name="woocommerce_rede_credit_installment_interest_example_field" id="woocommerce_rede_credit_installment_interest_example_field" value="1"
                  data-title-description="${lknPhpProFieldsVariables.installmentInterestDataDescription}" lkn-is-pro="true"> <span>${lknPhpProFieldsVariables.interestOnInstallments}</span></label><br>
                  <p class="description">
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