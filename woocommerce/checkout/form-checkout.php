<?php
/**
 * Custom checkout form for BonosPremium
 * Uses WooCommerce hooks for all sections
 */
if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>

<?php wc_get_template('checkout/form-login.php', array('checkout' => $checkout)); ?>

<?php if (wc_coupons_enabled()) : ?>
<div class="woocommerce-form-coupon-toggle">
    <div class="woocommerce-info">
        <?php esc_html_e('¿Tienes un código de descuento?', 'woocommerce'); ?>
        <a href="#" class="showcoupon"><?php esc_html_e('Haz clic aquí para introducirlo', 'woocommerce'); ?></a>
    </div>
</div>
<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">
    <p><?php esc_html_e('Si tienes un código de descuento, por favor introdúcelo a continuación.', 'woocommerce'); ?></p>
    <p class="form-row form-row-first">
        <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Código de descuento', 'woocommerce'); ?>" id="coupon_code" value="" />
    </p>
    <p class="form-row form-row-last">
        <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Aplicar cupón', 'woocommerce'); ?>"><?php esc_html_e('Aplicar cupón', 'woocommerce'); ?></button>
    </p>
    <div class="clear"></div>
</form>
<?php endif; ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <div class="bp-checkout-section">
        <?php do_action('woocommerce_checkout_billing', $checkout); ?>
    </div>

    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Información adicional</h3>
        <?php do_action('woocommerce_checkout_shipping', $checkout); ?>
    </div>

    <div class="bp-checkout-card">
        <h3 class="bp-section-title">Tu pedido</h3>
        <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>

</form>

<script>
jQuery(document).ready(function($) {
    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp(200);
        $(this).closest('.wc_payment_method').find('.bp-payment-box').slideDown(200);
    });
    $('input[name="payment_method"]:checked').closest('.wc_payment_method').find('.bp-payment-box').show();
});
</script>
