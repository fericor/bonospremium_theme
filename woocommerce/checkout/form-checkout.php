<?php
/**
 * Custom checkout template for BonosPremium
 * Finalizar compra
 */
get_header(); ?>

<main class="bp-main-content bp-checkout-page">
    <div class="bp-container">
        <h1 class="bp-page-title">Finalizar compra</h1>

        <?php if (!is_user_logged_in() && 'no' === get_option('woocommerce_enable_checkout_login_reminder')) : ?>
            <div class="bp-checkout-login">
                <p>¿Ya tienes cuenta? <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Inicia sesión</a></p>
            </div>
        <?php endif; ?>

        <?php if (WC()->cart && !WC()->cart->is_empty()) : ?>
            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
                <div class="bp-checkout-grid">
                    <!-- Columna izquierda: formulario -->
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

                    <!-- Columna derecha: resumen + pago -->
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
                                        $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
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

                                <button type="submit" class="bp-place-order" name="woocommerce_checkout_place_order" id="place_order">
                                    <i class="fas fa-lock"></i> Realizar pedido
                                </button>

                                <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php else : ?>
            <div class="bp-checkout-empty">
                <p>Tu carrito está vacío.</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="bp-btn-primary">Ver productos</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.bp-page-title { font-size: 24px; font-weight: 700; color: var(--bp-text); margin: 0 0 24px; }
.bp-checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: start; }
.bp-checkout-login { background: var(--bp-card-bg); border: 1px solid var(--bp-border); border-radius: 16px; padding: 20px 24px; margin-bottom: 24px; text-align: center; }
.bp-checkout-login a { color: var(--bp-primary); font-weight: 600; text-decoration: none; }
.bp-checkout-login a:hover { text-decoration: underline; }
.bp-section-title { font-size: 16px; font-weight: 700; color: var(--bp-text); margin: 0 0 16px; }

/* --- Secciones del formulario --- */
.bp-checkout-section {
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 16px; padding: 24px; margin-bottom: 20px;
}
.bp-checkout-form .form-row { margin-bottom: 14px; }
.bp-checkout-form label { font-size: 14px; font-weight: 500; color: var(--bp-text-light); margin-bottom: 4px; display: block; }
.bp-checkout-form input,
.bp-checkout-form select,
.bp-checkout-form textarea {
    width: 100%; padding: 10px 14px;
    border: 1px solid var(--bp-border); border-radius: 10px;
    font-size: 14px; background: var(--bp-bg); color: var(--bp-text);
    outline: none; transition: border-color .2s;
}
.bp-checkout-form input:focus,
.bp-checkout-form select:focus,
.bp-checkout-form textarea:focus { border-color: var(--bp-primary); }

/* --- Sidebar resumen --- */
.bp-checkout-sidebar { position: sticky; top: 100px; }
.bp-checkout-card {
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 16px; padding: 24px;
}
.bp-checkout-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px; }
.bp-checkout-table th { text-align: left; font-weight: 600; color: var(--bp-text-muted); padding: 8px 4px; border-bottom: 1px solid var(--bp-border); }
.bp-checkout-table td { padding: 10px 4px; border-bottom: 1px solid var(--bp-border); color: var(--bp-text); }
.bp-checkout-table tfoot th { font-weight: 500; color: var(--bp-text-light); }
.bp-checkout-table tfoot td { font-weight: 600; }
.bp-checkout-table .order-total th,
.bp-checkout-table .order-total td { font-weight: 700; font-size: 16px; color: var(--bp-text); border-top: 2px solid var(--bp-border); }
.bp-text-right { text-align: right; }

/* --- Métodos de pago --- */
.bp-payment-methods { list-style: none; padding: 0; margin: 0 0 16px; }
.bp-payment-method {
    padding: 12px 16px; border: 1px solid var(--bp-border);
    border-radius: 12px; margin-bottom: 8px;
    background: var(--bp-bg); transition: border-color .2s;
}
.bp-payment-method:has(input:checked) { border-color: var(--bp-primary); }
.bp-payment-method label {
    display: flex; align-items: center; gap: 10px;
    font-size: 14px; font-weight: 500; color: var(--bp-text); cursor: pointer;
}
.bp-payment-method input[type="radio"] { accent-color: var(--bp-primary); width: 18px; height: 18px; }
.bp-payment-box { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--bp-border); font-size: 13px; color: var(--bp-text-light); }

/* --- Botón realizar pedido --- */
.bp-place-order {
    width: 100%; padding: 16px;
    background: var(--bp-primary); color: #fff;
    border: none; border-radius: 12px;
    font-size: 16px; font-weight: 600;
    cursor: pointer; transition: background .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.bp-place-order:hover { background: var(--bp-primary-dark); }

/* --- Botones genéricos --- */
.bp-btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px; border-radius: 12px;
    font-size: 14px; font-weight: 600; text-decoration: none; transition: all .2s;
    background: var(--bp-primary); color: #fff;
}
.bp-btn-primary:hover { background: var(--bp-primary-dark); color: #fff; }

/* --- Checkout vacío --- */
.bp-checkout-empty { text-align: center; padding: 60px 20px; color: var(--bp-text-light); }

/* --- Responsive --- */
@media (max-width: 768px) {
    .bp-checkout-grid { grid-template-columns: 1fr; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle payment method fields
    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp();
        $(this).closest('.bp-payment-method').find('.bp-payment-box').slideDown();
    });
});
</script>

<?php get_footer(); ?>
