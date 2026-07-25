<?php
/**
 * Custom thank you / order received page
 * BonosPremium Theme
 */
get_header(); ?>

<main class="bp-main-content">
    <div class="bp-container">
        <div class="bp-thankyou-wrap">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="bp-thankyou-header">
                    <div class="bp-thankyou-icon">&#10003;</div>
                    <h1 class="bp-thankyou-title">¡Gracias por tu compra!</h1>
                    <p class="bp-thankyou-sub">Tu pedido ha sido recibido y está siendo procesado.</p>
                </div>

                <?php woocommerce_order_details_table(); ?>

                <div class="bp-thankyou-actions">
                    <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="bp-btn-primary">Ir a mi cuenta</a>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="bp-btn-secondary">Seguir comprando</a>
                </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
