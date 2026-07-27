<?php
/**
 * Custom checkout form for BonosPremium
 */
if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>

<?php wc_get_template('checkout/form-login.php', array('checkout' => $checkout)); ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
<<<<<<< HEAD
    <div class="bp-checkout-grid">
        <div class="bp-checkout-form">
            <div class="bp-checkout-section">
                <?php do_action('woocommerce_checkout_billing', $checkout); ?>
            </div>
            <div class="bp-checkout-section">
                <h3 class="bp-section-title">Información adicional</h3>
                <?php do_action('woocommerce_checkout_shipping', $checkout); ?>
            </div>
        </div>
        <div class="bp-checkout-sidebar">
            <div class="bp-checkout-card">
                <h3 class="bp-section-title">Tu pedido</h3>
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>
        </div>
=======

    <!-- Datos de facturación -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Datos de facturación</h3>
        <?php do_action('woocommerce_checkout_billing', $checkout); ?>
>>>>>>> 484df81655fd6932c3093d2dd341a584bdb5dd86
    </div>

    <!-- Información adicional -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Información adicional</h3>
        <?php do_action('woocommerce_checkout_shipping', $checkout); ?>
    </div>

    <!-- Tu pedido -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Tu pedido</h3>
        <?php wc_get_template('checkout/review-order.php'); ?>
    </div>

    <!-- Cupón descuento -->
    <?php bp_checkout_coupon_form(); ?>

    <!-- Métodos de pago -->
    <div class="bp-checkout-section">
        <?php woocommerce_checkout_payment(); ?>
    </div>

</form>

<script>
jQuery(function($) {
    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp();
        $(this).closest('.bp-payment-method').find('.bp-payment-box').slideDown();
    });
});
</script>
