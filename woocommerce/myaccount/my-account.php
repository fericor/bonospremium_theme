<?php
/**
 * Custom My Account page - App Style v2
 * BonosPremium Theme
 */
get_header(); ?>
<main class="bp-main-content bp-account-page">
    <div class="bp-container">
        <div class="bp-account-app">
            <?php if (is_user_logged_in()) : 
                $current_user = wp_get_current_user();
                $menu_items = wc_get_account_menu_items();
                $icons = [
                    'dashboard'       => 'fa-th-large',
                    'orders'          => 'fa-ticket-alt',
                    'downloads'       => 'fa-download',
                    'edit-address'    => 'fa-map-marker-alt',
                    'payment-methods' => 'fa-credit-card',
                    'edit-account'    => 'fa-user-cog',
                    'favoritos'       => 'fa-heart',
                    'customer-logout' => 'fa-sign-out-alt',
                ];
            ?>
                <div class="bp-prof-header">
                    <div class="bp-prof-avatar"><?php echo get_avatar($current_user->ID, 72); ?></div>
                    <div class="bp-prof-info">
                        <h2><?php echo esc_html($current_user->display_name); ?></h2>
                        <p><?php echo esc_html($current_user->user_email); ?></p>
                    </div>
                </div>
                <nav class="bp-prof-menu">
                    <?php foreach ($menu_items as $endpoint => $label) : 
                        $icon = isset($icons[$endpoint]) ? $icons[$endpoint] : 'fa-circle';
                    ?>
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="bp-prof-menu-item <?php echo $endpoint === 'customer-logout' ? 'bp-prof-logout' : ''; ?>">
                            <span class="bp-prof-icon"><i class="fas <?php echo $icon; ?>"></i></span>
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
                    <div class="bp-auth-brand">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/logo_rectangulo.png" alt="BonosPremium" class="bp-auth-logo" />
                    </div>
                    <div class="bp-auth-tabs">
                        <button class="bp-auth-tab active" data-tab="login">Acceder</button>
                        <button class="bp-auth-tab" data-tab="register">Registrarse</button>
                    </div>
                    <div class="bp-auth-panels">
                        <div class="bp-auth-panel active" id="bp-login-panel">
                            <?php woocommerce_login_form(); ?>
                        </div>
                        <div class="bp-auth-panel" id="bp-register-panel" style="display:none;">
                            <?php woocommerce_register_form(); ?>
                        </div>
                    </div>
                </div>
                <script>
                jQuery(function($) {
                    $('.bp-auth-tab').on('click', function() {
                        $('.bp-auth-tab').removeClass('active');
                        $(this).addClass('active');
                        $('.bp-auth-panel').hide();
                        if ($(this).data('tab') === 'login') {
                            $('#bp-login-panel').show();
                        } else {
                            $('#bp-register-panel').show();
                        }
                    });
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
