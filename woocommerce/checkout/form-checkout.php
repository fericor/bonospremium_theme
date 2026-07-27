<?php
/**
 * Custom checkout form for BonosPremium
 */
if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>

<?php wc_get_template('checkout/form-login.php', array('checkout' => $checkout)); ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    <div class="bp-checkout-grid">
        <div class="bp-checkout-form">
            <div class="bp-checkout-section">
                <?php do_action('woocommerce_checkout_billing', $checkout); ?>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="bp-checkout-section">
        <h3 class="bp-section-title">Información adicional</h3>
        <?php wc_get_template('checkout/form-shipping.php', array('checkout' => $checkout)); ?>
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
    // Términos marcado por defecto
    $('#terms').prop('checked', true);

    $('input[name="payment_method"]').on('change', function() {
        $('.bp-payment-box').slideUp();
        $(this).closest('.bp-payment-method').find('.bp-payment-box').slideDown();
    });
});
</script>
