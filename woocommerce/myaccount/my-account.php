<?php
/**
 * Custom My Account page - App Style
 * BonosPremium Theme
 */
get_header(); ?>
<main class="bp-main-content bp-account-page">
    <div class="bp-container">
        <div class="bp-account-app">
            <?php if (is_user_logged_in()) : 
                $current_user = wp_get_current_user();
            ?>
                <div class="bp-prof-header">
                    <div class="bp-prof-avatar"><?php echo get_avatar($current_user->ID, 72); ?></div>
                    <div class="bp-prof-info">
                        <h2><?php echo esc_html($current_user->display_name); ?></h2>
                        <p><?php echo esc_html($current_user->user_email); ?></p>
                    </div>
                </div>
                <nav class="bp-prof-menu">
                    <?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="bp-prof-menu-item">
                            <span class="bp-prof-label"><?php echo esc_html($label); ?></span>
                            <span class="bp-prof-arrow">›</span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="bp-prof-content">
                    <?php woocommerce_account_content(); ?>
                </div>
            <?php else : ?>
                <div class="bp-auth-app">
                    <h2 style="text-align:center;padding:40px 20px;color:#039CDC;">Mi Cuenta</h2>
                    <?php woocommerce_account_content(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
