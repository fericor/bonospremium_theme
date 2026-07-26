<?php
/**
 * Custom checkout form for BonosPremium
 * Uses WooCommerce hooks for all sections
 */
if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>

<?php wc_get_template('checkout/form-login.php', array('checkout' => $checkout)); ?>

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
