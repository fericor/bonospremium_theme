<?php
/**
 * Custom My Account page
 * BonosPremium Theme - Login & Register
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
                <!-- Login + Register side by side -->
                <div class="bp-auth-grid">
                    <!-- Login -->
                    <div class="bp-auth-card">
                        <h2 class="bp-auth-title">Iniciar sesión</h2>
                        <form class="bp-auth-form" method="post">
                            <?php do_action('woocommerce_login_form_start'); ?>

                            <div class="bp-field">
                                <label for="username">Usuario o email <span class="required">*</span></label>
                                <input type="text" name="username" id="username" class="bp-input" required />
                            </div>
                            <div class="bp-field">
                                <label for="password">Contraseña <span class="required">*</span></label>
                                <input type="password" name="password" id="password" class="bp-input" required />
                            </div>

                            <?php do_action('woocommerce_login_form'); ?>

                            <div class="bp-field-row">
                                <label class="bp-checkbox-label">
                                    <input type="checkbox" name="rememberme" id="rememberme" value="forever" />
                                    <span>Recordarme</span>
                                </label>
                            </div>

                            <button type="submit" class="bp-btn-primary bp-auth-btn" name="login" value="Login">
                                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                            </button>

                            <p class="bp-auth-lostpass">
                                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">¿Olvidaste tu contraseña?</a>
                            </p>

                            <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                            <?php do_action('woocommerce_login_form_end'); ?>
                        </form>
                    </div>

                    <!-- Register -->
                    <div class="bp-auth-card">
                        <h2 class="bp-auth-title">Crear cuenta</h2>
                        <form class="bp-auth-form" method="post">
                            <?php do_action('woocommerce_register_form_start'); ?>

                            <div class="bp-field">
                                <label for="reg_email">Email <span class="required">*</span></label>
                                <input type="email" name="email" id="reg_email" class="bp-input" required />
                            </div>
                            <div class="bp-field">
                                <label for="reg_password">Contraseña <span class="required">*</span></label>
                                <input type="password" name="password" id="reg_password" class="bp-input" required />
                            </div>

                            <?php do_action('woocommerce_register_form'); ?>

                            <button type="submit" class="bp-btn-primary bp-auth-btn" name="register" value="Register">
                                <i class="fas fa-user-plus"></i> Crear cuenta
                            </button>

                            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                            <?php do_action('woocommerce_register_form_end'); ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.bp-account-wrap { max-width: 960px; margin: 40px auto; }

/* --- Auth grid: login + register side by side --- */
.bp-auth-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 32px;
}
.bp-auth-card {
    background: var(--bp-card-bg);
    border: 1px solid var(--bp-border);
    border-radius: 20px;
    padding: 36px;
}
.bp-auth-title {
    font-size: 22px; font-weight: 700; color: var(--bp-text);
    margin: 0 0 24px; text-align: center;
}

/* --- Form fields --- */
.bp-auth-form { display: flex; flex-direction: column; gap: 16px; }
.bp-field { display: flex; flex-direction: column; gap: 6px; }
.bp-field label {
    font-size: 14px; font-weight: 500; color: var(--bp-text-light);
}
.bp-field .required { color: #ef4444; }
.bp-input {
    width: 100%; padding: 12px 16px;
    border: 1px solid var(--bp-border); border-radius: 10px;
    font-size: 15px; background: var(--bp-bg); color: var(--bp-text);
    outline: none; transition: border-color .2s;
}
.bp-input:focus { border-color: var(--bp-primary); }
.bp-field-row { display: flex; align-items: center; gap: 8px; }
.bp-checkbox-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; color: var(--bp-text-light); cursor: pointer;
}
.bp-checkbox-label input[type="checkbox"] {
    width: 18px; height: 18px; accent-color: var(--bp-primary);
}

/* --- Buttons --- */
.bp-auth-btn {
    width: 100%; padding: 14px; justify-content: center;
    font-size: 15px; border: none; cursor: pointer;
}
.bp-btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 600;
    text-decoration: none; transition: all .2s; border: none; cursor: pointer;
}
.bp-btn-primary { background: var(--bp-primary); color: #fff; }
.bp-btn-primary:hover { background: var(--bp-primary-dark); color: #fff; }

/* --- Lost password --- */
.bp-auth-lostpass { text-align: center; margin: 0; }
.bp-auth-lostpass a { color: var(--bp-primary); font-size: 13px; text-decoration: none; }
.bp-auth-lostpass a:hover { text-decoration: underline; }

/* --- Logged in header --- */
.bp-account-header {
    display: flex; align-items: center; gap: 20px;
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 20px; padding: 28px 32px; margin-bottom: 32px;
}
.bp-account-avatar img { border-radius: 50%; width: 60px; height: 60px; object-fit: cover; }
.bp-account-greeting h1 { font-size: 22px; font-weight: 700; color: var(--bp-text); margin: 0; }
.bp-account-greeting p { color: var(--bp-text-light); margin: 4px 0 0; }

/* --- Account nav --- */
.bp-account-grid { display: grid; grid-template-columns: 220px 1fr; gap: 32px; }
.bp-account-nav { display: flex; flex-direction: column; gap: 4px; }
.bp-account-nav-item {
    padding: 10px 16px; border-radius: 10px;
    font-size: 14px; font-weight: 500; color: var(--bp-text-light);
    text-decoration: none; transition: all .2s;
    display: flex; align-items: center; gap: 10px;
}
.bp-account-nav-item:hover { background: #f3f4f6; color: var(--bp-text); }
.bp-account-nav-item.active { background: var(--bp-primary); color: #fff; }
.bp-account-content {
    background: var(--bp-card-bg); border: 1px solid var(--bp-border);
    border-radius: 20px; padding: 28px 32px; min-height: 300px;
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .bp-auth-grid { grid-template-columns: 1fr; }
    .bp-account-grid { grid-template-columns: 1fr; }
    .bp-account-nav { flex-direction: row; overflow-x: auto; padding-bottom: 8px; }
    .bp-account-nav-item { white-space: nowrap; }
}
</style>

<?php get_footer(); ?>
