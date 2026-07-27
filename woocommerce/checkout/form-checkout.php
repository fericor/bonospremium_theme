<?php
/**
 * Custom checkout form for BonosPremium
 */
if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>

<?php wc_get_template('checkout/form-login.php', array('checkout' => $checkout)); ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <!-- Datos de facturación -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Datos de facturación</h3>
        <?php do_action('woocommerce_checkout_billing', $checkout); ?>
    </div>

    <!-- Información adicional -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Información adicional</h3>
        <?php do_action('woocommerce_checkout_shipping', $checkout); ?>
    </div>

    <!-- Tu pedido -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Tu pedido</h3>
        <?php
        // Solo la tabla del pedido (sin cupón ni pago)
        remove_action('woocommerce_checkout_order_review', 'bp_checkout_coupon_form', 15);
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
        woocommerce_order_review();
        add_action('woocommerce_checkout_order_review', 'bp_checkout_coupon_form', 15);
        add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
        ?>
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
