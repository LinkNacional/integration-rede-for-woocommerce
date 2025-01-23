<h3><?php esc_attr_e($title, 'integration-rede-for-woocommerce'); ?>
</h3>
<table>
    <tbody>
        <?php array_map(function ($meta_key, $label) use ($order): void {
            $meta_value = $order->get_meta($meta_key);
            if (! empty($meta_value)) : ?>
        <tr>
            <td><?php echo esc_attr__($label, 'woo-rede'); ?>
            </td>
            <td><?php echo esc_attr__($meta_value, 'woo-rede'); ?>
            </td>
        </tr>
        <?php endif;
        }, array_keys($metaKeys), $metaKeys);?>
    </tbody>
</table>