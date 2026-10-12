<?php
/**
 * Custom My Account page - App Style v2
 * BonosPremium Theme
 */
get_header(); ?>
<main class="bp-main-content">
    <div class="bp-container">
        <div class="bp-account-app">
            <?php if (is_user_logged_in()) : 
                $current_user = wp_get_current_user();
                $menu_items = wc_get_account_menu_items();
                // Icono y grupo por endpoint
                $icons = [
                    'dashboard'          => 'fa-th-large',
                    'orders'             => 'fa-ticket-alt',
                    'downloads'          => 'fa-download',
                    'edit-address'       => 'fa-map-marker-alt',
                    'payment-methods'    => 'fa-credit-card',
                    'edit-account'       => 'fa-user-cog',
                    'customer-logout'    => 'fa-sign-out-alt',
                    'credito-bonospremium' => 'fa-wallet',
                    'wlfmc-wishlist'     => 'fa-heart',
                    'wpf-delete-account' => 'fa-trash-alt',
                ];
                // Agrupamos: [nombre_grupo => [slug=>label]]
                $menu_groups = [
                    'Mi cuenta' => [],
                    'Mi actividad' => [],
                    'Ajustes' => [],
                ];
                $group_of = [
                    'dashboard'       => 'Mi actividad',
                    'orders'          => 'Mi actividad',
                    'downloads'       => 'Mi actividad',
                    'edit-address'    => 'Mi cuenta',
                    'edit-account'    => 'Ajustes',
                    'payment-methods' => 'Ajustes',
                ];
                foreach ($menu_items as $endpoint => $label) {
                    if ($endpoint === 'customer-logout') continue; // el logout va después
                    $g = isset($group_of[$endpoint]) ? $group_of[$endpoint] : 'Mi cuenta';
                    $menu_groups[$g][$endpoint] = $label;
                }
            ?>
                <div class="bp-account-layout">
                <nav class="bp-prof-menu">
                    <div class="bp-prof-header">
                        <div class="bp-prof-avatar">
                            <?php echo get_avatar( get_current_user_id(), 64 ); ?>
                        </div>
                        <div class="bp-prof-id">
                            <span class="bp-prof-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                            <span class="bp-prof-mail"><?php echo esc_html( wp_get_current_user()->user_email ); ?></span>
                        </div>
                    </div>
                    <?php foreach ($menu_groups as $group_name => $items) : if (empty($items)) continue; ?>
                        <div class="bp-prof-group">
                            <span class="bp-prof-group-title"><?php echo esc_html($group_name); ?></span>
                            <?php foreach ($items as $endpoint => $label) :
                                $icon = isset($icons[$endpoint]) ? $icons[$endpoint] : 'fa-circle';
                                $is_active = is_wc_endpoint_url( $endpoint ) || ( $endpoint === 'dashboard' && is_account_page() && ! is_wc_endpoint_url() );
                            ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="bp-prof-menu-item<?php echo esc_attr($is_active ? ' active' : ''); ?>">
                                    <span class="bp-prof-icon"><i class="fas <?php echo $icon; ?>"></i></span>
                                    <span class="bp-prof-label"><?php echo esc_html($label); ?></span>
                                    <span class="bp-prof-arrow">›</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="bp-prof-group">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('customer-logout')); ?>" class="bp-prof-menu-item bp-prof-logout">
                            <span class="bp-prof-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span class="bp-prof-label">Cerrar sesión</span>
                            <span class="bp-prof-arrow">›</span>
                        </a>
                    </div>
                </nav>
                <div class="bp-prof-content">
                <?php
                // El dashboard (resumen + tarjetas) se renderiza en el override
                // woocommerce/myaccount/dashboard.php — HTML personalizado (09/09).
                // Aquí solo se llama a woocommerce_account_content(), que resuelve
                // el endpoint y usa ese template.
                    woocommerce_account_content();
                ?>
                </div>
                </div>
            <?php else : ?>
                <div class="bp-auth-app">
                    <?php $bp_es_logout = isset($_GET['bp_logout']) || isset($_GET['loggedout']) || isset($_GET['account-logout']); ?>
                    <?php if ($bp_es_logout) : ?>
                        <div class="bp-auth-logout-msg">
                            <div class="bp-auth-logout-icon"><i class="fas fa-check-circle"></i></div>
                            <h2>Has cerrado la sesión</h2>
                            <p>Tu sesión se ha cerrado correctamente. ¡Hasta pronto!</p>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="bp-auth-btn">Volver al inicio</a>
                        </div>
                    <?php endif; ?>
                    <?php if (!$bp_es_logout) : ?>
                    <div class="bp-auth-content">
                        <?php
                        // Forzar el formulario de login SIEMPRE (evita que WooCommerce muestre
                        // el dashboard residual "Hola..." cuando hay una cookie de sesión a medias)
                        if (function_exists('wc_get_template')) {
                            wc_get_template('myaccount/form-login.php');
                        } else {
                            woocommerce_account_content();
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
