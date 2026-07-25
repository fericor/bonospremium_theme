<?php
/**
 * Custom My Account page
 * BonosPremium Theme
 */
get_header(); ?>

<main class="bp-main-content bp-account-page">
    <div class="bp-container">
        <div class="bp-account-wrap">
            <?php if (is_user_logged_in()) : 
                $current_user = wp_get_current_user();
            ?>
                <div class="bp-account-header">
                    <div class="bp-account-avatar">
                        <?php echo get_avatar($current_user->ID, 60); ?>
                    </div>
                    <div class="bp-account-greeting">
                        <h1>Hola, <?php echo esc_html($current_user->display_name); ?></h1>
                        <p><?php echo esc_html($current_user->user_email); ?></p>
                    </div>
                </div>

                <div class="bp-account-grid">
                    <nav class="bp-account-nav">
                        <?php
                        $items = wc_get_account_menu_items();
                        $current = isset($items[WC()->query->get_current_endpoint()]) ? WC()->query->get_current_endpoint() : 'dashboard';
                        foreach ($items as $endpoint => $label) :
                            $active = (WC()->query->get_current_endpoint() === $endpoint || 
                                      (empty(WC()->query->get_current_endpoint()) && $endpoint === 'dashboard'));
                        ?>
                            <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" 
                               class="bp-account-nav-item <?php echo $active ? 'active' : ''; ?>">
                                <span class="bp-nav-icon">
                                    <?php
                                    $icons = [
                                        'dashboard' => '&#8984;',
                                        'orders' => '&#128230;',
                                        'downloads' => '&#11015;',
                                        'edit-address' => '&#128205;',
                                        'payment-methods' => '&#128179;',
                                        'edit-account' => '&#128100;',
                                        'customer-logout' => '&#8594;',
                                    ];
                                    echo $icons[$endpoint] ?? '&#8226;';
                                    ?>
                                </span>
                                <?php echo esc_html($label); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="bp-account-content">
                        <?php woocommerce_account_content(); ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="bp-account-login-form">
                    <?php woocommerce_login_form(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
