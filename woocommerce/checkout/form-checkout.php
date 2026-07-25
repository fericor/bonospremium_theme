<?php
/**
 * Custom checkout form content for BonosPremium
 * This template renders ONLY the checkout form (no wrapper - handled by template-checkout.php)
 */
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in() && 'no' === get_option('woocommerce_enable_checkout_login_reminder')) : ?>
    <div class="bp-checkout-login">
        <p>¿Ya tienes cuenta? <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Inicia sesión</a></p>
    </div>
<?php endif; ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    <div class="bp-checkout-grid">
        <div class="bp-checkout-form">
            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                <div class="bp-checkout-section">
                    <h3 class="bp-section-title">Envío</h3>
                    <?php wc_cart_totals_shipping_html(); ?>
                </div>
            <?php endif; ?>

            <div class="bp-checkout-section">
                <h3 class="bp-section-title">Datos de facturación</h3>
                <?php do_action('woocommerce_checkout_billing'); ?>
            </div>

            <div class="bp-checkout-section">
                <h3 class="bp-section-title">Información adicional</h3>
                <?php do_action('woocommerce_checkout_shipping'); ?>
            </div>
        </div>

        <div class="bp-checkout-sidebar">
            <div class="bp-checkout-card">
                <h3 class="bp-section-title">Tu pedido</h3>

                <table class="bp-checkout-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="bp-text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_name = $_product->get_name();
                        ?>
                        <tr>
                            <td><?php echo esc_html($product_name); ?> <strong>× <?php echo esc_html($cart_item['quantity']); ?></strong></td>
                            <td class="bp-text-right"><?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Subtotal</th>
                            <td class="bp-text-right"><?php wc_cart_totals_subtotal_html(); ?></td>
                        </tr>
                        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                        <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                            <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                            <td class="bp-text-right"><?php wc_cart_totals_coupon_html($coupon); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                        <tr class="shipping">
                            <th>Envío</th>
                            <td class="bp-text-right"><?php wc_cart_totals_shipping_html(); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="order-total">
                            <th>Total</th>
                            <td class="bp-text-right bp-total"><?php wc_cart_totals_order_total_html(); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="bp-checkout-payment">
                    <?php if (WC()->cart->needs_payment()) : ?>
                        <ul class="bp-payment-methods">
                            <?php foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) : ?>
                            <li class="bp-payment-method">
                                <label>
                                    <input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> />
                                    <?php echo esc_html($gateway->get_title()); ?>
                                </label>
                                <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                                    <div class="bp-payment-box" <?php if (!$gateway->chosen) echo 'style="display:none;"'; ?>>
                                        <?php $gateway->payment_fields(); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php do_action('woocommerce_checkout_terms_and_conditions'); ?>
                    <button type="submit" class="bp-place-order" name="woocommerce_checkout_place_order" id="place_order">Realizar pedido</button>
                    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
jQuery(document).ready(function($) {
    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp();
        $(this).closest('.bp-payment-method').find('.bp-payment-box').slideDown();
    });
});
</script>
