<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}
?>
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"
>
<div id="pix_new_content">
    <div class="container_pix">
        <div class="span_title_container">
            <span class="span_title_text"><?php echo esc_attr__('Instructions', 'woo-rede'); ?></span>
        </div>
        <ol>
            <li class="span_content_text"><?php echo esc_attr__('Open your phone\'s camera and point it at the QR Code on the screen.', 'woo-rede'); ?></li>
            <li class="span_content_text"><?php echo esc_attr__('You will be redirected to the bank app to complete the payment.', 'woo-rede'); ?></li>
            <li class="span_content_text"><?php echo esc_attr__('Done! Now just confirm the details and complete the payment with Pix.', 'woo-rede'); ?></li>
        </ol>
        <div class="schedule_check_container">
            <span class="schedule_text"><?php echo esc_attr__('Next verification in (No. of attempts: 5):', 'woo-rede'); ?></span>
            <span id="timer">0s</span>
        </div>

        <div class="payment_check_container">
            <button
                class="payment_check_button"
                disabled
            ><?php echo esc_attr__('I have already paid the PIX', 'woo-rede'); ?></button>
        </div>
        <span class="payment_check_text"><?php echo esc_attr__('? - By clicking this button, we will check if the payment has been successfully confirmed.', 'woo-rede'); ?></span>
    </div>
    <div class="container_pix">
        <div id="span_container">
            <span class="span_title_value"><?php echo esc_attr__('Total', 'woo-rede'); ?></span>
            <span
                class="span_total_value"
                id="pix_page_currency_text"
            ><?php echo wp_kses_post($currencyTxt); ?></span>
            <span class="span_date"><?php echo esc_attr($dueDateMsg); ?></span>
        </div>
        <div id="copy_container">
            <input
                type="text"
                class="input_copy_code"
                readonly
                style="border: none; background-color: #D9D9D9;"
                value="<?php echo esc_attr(htmlspecialchars($donKey, ENT_QUOTES, 'UTF-8')); ?>"
            >

            <input
                type="hidden"
                id="donationId"
                value=<?php echo esc_attr($donationId); ?>
            >
            <button class="button_copy_code"><?php echo esc_attr__('COPY', 'woo-rede'); ?></button>
        </div>
        <div id="pix_page_qr_code">
            <img
                src="data:image/png;base64,<?php echo esc_attr($donQrCode); ?>"
                class="pix_img"
            >
        </div>
    </div>
    <div class="share_container">
        <button
            class="share_button"
            style="background-color: transparent"
        >
            <img
                src=<?php echo esc_attr($filePath); ?>
                alt="icon"
            >
        </button>
    </div>
</div>