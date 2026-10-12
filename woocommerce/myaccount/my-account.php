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

                // ===== ORDEN MANUAL DEL MENÚ =====
                // Tú decides aquí: grupos y, dentro de cada grupo, el orden de los endpoints.
                // Los labels reales se rellenan luego desde WooCommerce.
                $orden = [
                    'Mi cuenta' => [
                        'dashboard',
                        'orders',
                        'downloads',
						'credito-bonospremium',
						'wlfmc-wishlist',
                    ],
                    'Ajustes' => [
                        'edit-address',
                        'edit-account',
                        'payment-methods',
                        'wpf-delete-account',
                    ],
                ];

                // Construimos $menu_groups respetando el orden de $orden
                $menu_groups = [];
                foreach ($orden as $group_name => $endpoints) {
                    $menu_groups[$group_name] = [];
                    foreach ($endpoints as $endpoint) {
                        if ($endpoint === 'customer-logout') continue;
                        if (isset($menu_items[$endpoint])) {
                            $menu_groups[$group_name][$endpoint] = $menu_items[$endpoint];
                        }
                    }
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
                                // ===== Detectar item activo =====
								$is_active = false;

								// 1) Endpoints estándar de WooCommerce
								if ( is_wc_endpoint_url( $endpoint ) ) {
									$is_active = true;
								}

								// 2) Dashboard (cuando no hay ningún endpoint activo)
								if ( $endpoint === 'dashboard' && is_account_page() && ! is_wc_endpoint_url() ) {
									$is_active = true;
								}

								// 3) Detección por URL actual (para endpoints personalizados / de plugins)
								$current_url = trailingslashit( home_url( add_query_arg( [], $GLOBALS['wp']->request ) ) );
								$item_url    = trailingslashit( wc_get_account_endpoint_url( $endpoint ) );

								if ( ! $is_active && strpos( $current_url, $item_url ) === 0 && $endpoint !== 'dashboard' ) {
									$is_active = true;
								}
                            ?>
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="bp-prof-menu-item<?php echo esc_attr($is_active ? ' active' : ''); ?>">
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
                // ===== RESUMEN DEL ESCRITORIO (solo en dashboard) =====
                $is_dashboard = is_account_page() && ! is_wc_endpoint_url();
                if ($is_dashboard) :

                    // Pedidos del usuario
                    $customer_orders = wc_get_orders([
                        'customer' => get_current_user_id(),
                        'limit'    => 1,
                        'status'   => array_keys(wc_get_order_statuses()),
                        'return'   => 'ids',
                    ]);
                    $order_count = count($customer_orders);

                    // Crédito BonosPremium
                    $wallet = function_exists('bp_get_user_wallet') ? bp_get_user_wallet(get_current_user_id()) : ['saldo' => 0];
                    $saldo_credito = $wallet['saldo'];

                    // Favoritos (plugin smart-wishlist / list de deseos)
                    $wishlist_count = 0;
                    $wishlist_ids = get_user_meta(get_current_user_id(), 'wlfmc_wishlist', true);
                    if (is_array($wishlist_ids)) $wishlist_count = count($wishlist_ids);

                    // Nombre del usuario
                    $nombre = wp_get_current_user()->display_name;
                    ?>
                <?php endif; ?>
                    <?php woocommerce_account_content(); ?>
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