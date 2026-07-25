<?php
/**
 * Custom checkout template for BonosPremium
 * Finalizar compra - con login, cupón colapsable y facturación
 */
get_header(); ?>

<main class="bp-main-content bp-checkout-page">
    <div class="bp-container">
        <h1 class="bp-page-title">Finalizar compra</h1>

        <?php if (!is_user_logged_in() && 'yes' === get_option('woocommerce_enable_checkout_login_reminder')) : ?>
            <div class="bp-checkout-login-toggle">
                <button type="button" class="bp-toggle-link" id="bp-show-login">
                    <i class="fas fa-user"></i> ¿Ya tienes cuenta? <strong>Inicia sesión</strong>
                </button>
                <div id="bp-login-form" class="bp-login-form-wrap" style="display:none;">
                    <?php
                    $info = __('If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce');
                    ?>
                    <form method="post" class="woocommerce-form woocommerce-form-login login">
                        <?php do_action('woocommerce_login_form_start'); ?>
                        <p class="form-row form-row-first">
                            <label for="username">Usuario o email <span class="required">*</span></label>
                            <input type="text" class="input-text" name="username" id="username" />
                        </p>
                        <p class="form-row form-row-last">
                            <label for="password">Contraseña <span class="required">*</span></label>
                            <input class="input-text" type="password" name="password" id="password" />
                        </p>
                        <div class="clear"></div>
                        <?php do_action('woocommerce_login_form'); ?>
                        <p class="form-row">
                            <button type="submit" class="bp-btn-primary" name="login" value="<?php esc_attr_e('Login', 'woocommerce'); ?>">
                                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                            </button>
                            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
                                <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" value="forever" /> <span>Recordarme</span>
                            </label>
                        </p>
                        <p class="lost_password">
                            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">¿Olvidaste tu contraseña?</a>
                        </p>
                        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                        <?php do_action('woocommerce_login_form_end'); ?>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (WC()->cart && !WC()->cart->is_empty()) : ?>

            <!-- Cupón descuento colapsable -->
            <div class="bp-coupon-section">
                <button type="button" class="bp-coupon-toggle">
                    <i class="fas fa-ticket-alt"></i> ¿Tienes un código de descuento?
                    <i class="fas fa-chevron-down bp-coupon-arrow"></i>
                </button>
                <div class="bp-coupon-body" style="display:none;">
                    <div class="bp-coupon-inner">
                        <input type="text" name="coupon_code" class="bp-coupon-input" 
                               placeholder="Introduce tu código de descuento" value="" />
                        <button type="submit" class="bp-btn-primary bp-coupon-btn" name="apply_coupon" value="Aplicar">
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>

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

/* --- Login toggle --- */
.bp-checkout-login-toggle { margin-bottom: 20px; }
.bp-toggle-link {
    width: 100%; padding: 14px 20px; text-align: center;
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 12px; color: var(--bp-text); font-size: 14px;
    cursor: pointer; transition: background .2s;
}
.bp-toggle-link:hover { background: #f9fafb; }
.bp-toggle-link strong { color: var(--bp-primary); }
.bp-login-form-wrap {
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-top: none; border-radius: 0 0 12px 12px;
    padding: 20px; margin-top: -6px;
}
.bp-login-form-wrap .form-row { margin-bottom: 14px; }
.bp-login-form-wrap label { font-size: 14px; font-weight: 500; color: var(--bp-text-light); display: block; margin-bottom: 4px; }
.bp-login-form-wrap input[type="text"],
.bp-login-form-wrap input[type="password"] {
    width: 100%; padding: 10px 14px;
    border: 1px solid var(--bp-border); border-radius: 10px;
    font-size: 14px; background: var(--bp-bg); color: var(--bp-text);
    outline: none; transition: border-color .2s;
}
.bp-login-form-wrap input:focus { border-color: var(--bp-primary); }
.bp-login-form-wrap .lost_password { margin: 12px 0 0; }
.bp-login-form-wrap .lost_password a { color: var(--bp-primary); font-size: 13px; text-decoration: none; }

/* --- Cupón colapsable --- */
.bp-coupon-section {
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 16px; overflow: hidden; margin-bottom: 20px;
}
.bp-coupon-toggle {
    width: 100%; padding: 16px 20px;
    display: flex; align-items: center; gap: 10px;
    background: transparent; border: none; cursor: pointer;
    font-size: 14px; font-weight: 600; color: var(--bp-text);
    transition: background .2s;
}
.bp-coupon-toggle:hover { background: #f9fafb; }
.bp-coupon-toggle i:first-child { color: var(--bp-primary); font-size: 16px; }
.bp-coupon-arrow { margin-left: auto; font-size: 12px; color: var(--bp-text-muted); transition: transform .3s; }
.bp-coupon-section.is-open .bp-coupon-arrow { transform: rotate(180deg); }
.bp-coupon-body { padding: 0 20px 16px; }
.bp-coupon-inner { display: flex; align-items: center; gap: 10px; }
.bp-coupon-input {
    flex: 1; min-width: 0; padding: 12px 16px;
    border: 1px solid var(--bp-border); border-radius: 10px;
    font-size: 14px; background: var(--bp-bg); color: var(--bp-text);
    outline: none; transition: border-color .2s;
}
.bp-coupon-input:focus { border-color: var(--bp-primary); }
.bp-coupon-btn { flex-shrink: 0; padding: 12px 24px; border: none; cursor: pointer; }

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

.bp-btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px; border-radius: 12px;
    font-size: 14px; font-weight: 600; text-decoration: none; transition: all .2s;
    background: var(--bp-primary); color: #fff; border: none; cursor: pointer;
}
.bp-btn-primary:hover { background: var(--bp-primary-dark); color: #fff; }

.bp-checkout-empty { text-align: center; padding: 60px 20px; color: var(--bp-text-light); }

@media (max-width: 768px) {
    .bp-checkout-grid { grid-template-columns: 1fr; }
    .bp-coupon-inner { flex-direction: column; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle payment method fields
    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp();
        $(this).closest('.bp-payment-method').find('.bp-payment-box').slideDown();
    });

    // Toggle login form
    $('#bp-show-login').on('click', function() {
        $('#bp-login-form').slideToggle(250);
    });

    // Cupón colapsable
    $('.bp-coupon-toggle').on('click', function() {
        var section = $(this).closest('.bp-coupon-section');
        section.find('.bp-coupon-body').slideToggle(250);
        section.toggleClass('is-open');
    });
});
</script>

<?php get_footer(); ?>
