<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h3><?php esc_attr($title); ?>
</h3>
<table>
    <tbody>
        <?php array_map(function ($meta_key, $label) use ($order): void {
            $meta_value = $order->get_meta($meta_key);
            if (! empty($meta_value)) : ?>
        <tr>
            <td><?php echo esc_attr($label); ?>
            </td>
            <td><?php echo esc_attr($meta_value); ?>
            </td>
        </tr>
        <?php endif;
        }, array_keys($metaKeys), $metaKeys);?>
    </tbody>
</table>