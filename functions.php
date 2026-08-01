<?php
/**
 * BonosPremium Lanzarote - Theme Functions
 */

// Definir versión del tema
define('BP_LZ_VERSION', '1.0.0');

// Soporte para WooCommerce
add_action('after_setup_theme', function() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    
    register_nav_menus([
        'primary' => __('Menú Principal', 'bonospremium'),
        'user-menu' => __('Menú de Usuario', 'bonospremium'),
        'footer-about' => __('Footer - Sobre Nosotros', 'bonospremium'),
        'footer-account' => __('Footer - Mi Cuenta', 'bonospremium'),
        'footer-offers' => __('Footer - Ofertas', 'bonospremium'),
    ]);
});

// Mejorar calidad de imágenes de productos
add_filter('woocommerce_get_image_size_shop_catalog', function($size) {
    return ['width' => 600, 'height' => 600, 'crop' => 1];
});
add_filter('woocommerce_get_image_size_shop_single', function($size) {
    return ['width' => 800, 'height' => 800, 'crop' => 0];
});
add_filter('woocommerce_get_image_size_shop_thumbnail', function($size) {
    return ['width' => 300, 'height' => 300, 'crop' => 1];
});
// JPEG quality al máximo
add_filter('jpeg_quality', function($quality) { return 90; });

// Cargar estilos y scripts
add_action('wp_enqueue_scripts', function() {
    // Google Fonts: Inter
    wp_enqueue_style('bp-lz-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', [], null);
    
    // Font Awesome
    wp_enqueue_style('bp-lz-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', [], '6.5.0');
    
    // Estilos del tema
    wp_enqueue_style('bp-lz-style', get_stylesheet_uri(), [], BP_LZ_VERSION);
    wp_enqueue_style('bp-lz-main', get_template_directory_uri() . '/assets/css/main.css', ['bp-lz-style'], BP_LZ_VERSION);
    
    // JavaScript
    wp_enqueue_script('bp-lz-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], BP_LZ_VERSION, true);
    
    // Localize script para AJAX
    wp_localize_script('bp-lz-main', 'bp_lz_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bp_lz_nonce'),
        'user_id' => get_current_user_id(),
        'wishlist' => bp_get_wishlist(),
        'wc_ajax_url' => WC()->ajax_url(),
        'coupon_nonce' => wp_create_nonce('apply-coupon'),
    ]);
});

// Clases del body
add_filter('body_class', function($classes) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        $classes[] = 'bp-shop-page';
    }
    if (is_product()) {
        $classes[] = 'bp-product-page';
    }
    if (is_cart() || is_checkout()) {
        $classes[] = 'bp-checkout-page';
    }
    if (is_account_page()) {
        $classes[] = 'bp-account-page';
    }
    return $classes;
});

// Redirigir al checkout después de añadir al carrito
add_filter('woocommerce_add_to_cart_redirect', function() {
    return wc_get_checkout_url();
});

// Modificar el loop de WooCommerce - 4 columnas
add_filter('loop_shop_columns', function() { return 4; });
add_filter('loop_shop_per_page', function() { return 10; });

// Nota: el toggle del login y auto-dismiss de notices están ahora en assets/js/main.js

// Mi cuenta - wrapper estilo app
add_action('template_redirect', function() {
    if (!is_account_page()) return;
    ob_start(function($html) {
        if (!is_user_logged_in()) {
            $html = str_replace('class="woocommerce"', 'class="woocommerce bp-account-app"', $html);
        }
        return $html;
    });
});

add_filter('woocommerce_output_related_products_args', function($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
});

// Quitar sidebar de WooCommerce en shop
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// Quitar badge de "¡Oferta!"
add_filter('woocommerce_sale_flash', '__return_false');

// Deshabilitar caché durante desarrollo
add_action('send_headers', function() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
});

// Ocultar wishlist duplicado del plugin YA NO se oculta: el plugin es el sistema principal
// de favoritos. Se eliminó la ocultación para que el corazón del plugin sea el visible.

// ===== QUANTITY EN PRODUCTO ÚNICO =====
// En single product: cantidad fija a 1, ocultar selector
add_filter('woocommerce_quantity_input_args', function($args, $product) {
    if (is_product() && $product->is_type('simple') && !$product->is_type('variable')) {
        $args['min_value'] = 1;
        $args['max_value'] = 1;
        $args['input_value'] = 1;
    }
    return $args;
}, 10, 2);

add_action('wp_head', function() {
    if (is_product()) {
        echo '<style>.bp-product-page .quantity { display: none !important; }</style>';
    }
});

// ===== INFINITE SCROLL =====
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
add_action('woocommerce_after_shop_loop', function() {
    global $wp_query;
    if ($wp_query->max_num_pages > 1) {
        echo '<div class="bp-load-more-wrap" data-page="1" data-max="' . $wp_query->max_num_pages . '">';
        echo '<div class="bp-load-more-spinner" style="display:none;"><span class="bp-spinner"></span> Cargando...</div>';
        echo '</div>';
    }
});

// Remove shop page title, description, result count, ordering
add_filter('woocommerce_show_page_title', '__return_false');
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// AJAX handler for load more
add_action('wp_ajax_bp_load_more', 'bp_load_more_products');
add_action('wp_ajax_nopriv_bp_load_more', 'bp_load_more_products');
function bp_load_more_products() {
    $page = (int)($_POST['page'] ?? 1);
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 10,
        'paged' => $page,
        'post_status' => 'publish',
    ];
    if (!empty($_POST['category'])) {
        $args['tax_query'] = [[
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => sanitize_text_field($_POST['category']),
        ]];
    }
    $loop = new WP_Query($args);
    ob_start();
    if ($loop->have_posts()) {
        while ($loop->have_posts()) { $loop->the_post();
            // Ejecutar el hook personalizado que genera el card
            do_action('woocommerce_before_shop_loop_item');
        }
    }
    wp_reset_postdata();
    echo ob_get_clean();
    wp_die();
}

// ===== PRODUCT LOOP PERSONALIZADO =====
// Reemplazar el UL/LI de WooCommerce por nuestro propio marcado
add_filter('woocommerce_product_loop_start', function($html) {
    return '<div class="bp-products-grid">';
});

add_filter('woocommerce_product_loop_end', function($html) {
    return '</div>';
});

// Quitar hooks default de WooCommerce
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

// Nuestro template de producto
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
add_action('woocommerce_before_shop_loop_item', function() {
    global $product;
    $city = get_field('localidad') ?: get_post_meta(get_the_ID(), 'localidad', true);
    $nombre_establecimiento = get_field('nombre_establecimiento') ?: get_post_meta(get_the_ID(), 'nombre_establecimiento', true);
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price() ?: $regular_price;
    
    echo '<div class="bp-product-card">';
    echo '<div class="bp-product-image-wrap">';
    echo '<a href="' . get_permalink() . '">';
    echo $product->get_image('medium_large');
    echo '</a>';
    echo '</div>';
    echo '<div class="bp-product-info">';
    echo '<h3 class="bp-product-title"><a href="' . get_permalink() . '">' . esc_html($nombre_establecimiento) . '</a></h3>';
    echo '<h4 class="bp-product-name">' . esc_html(get_the_title()) . '</h4>';
    echo '<div class="bp-product-bottom">';
    echo '<div class="bp-product-price">';
    if ($regular_price && $regular_price != $sale_price) {
        echo '<span class="bp-price-original">' . wc_price($regular_price) . '</span>';
    }
    echo '<span class="bp-price-sale">' . wc_price($sale_price) . '</span>';
    echo '</div>';
    if (!empty($city)) {
        echo '<span class="bp-product-city"><i class="fas fa-map-marker-alt"></i> ' . esc_html($city) . '</span>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
});

// Quitar el contenedor default de WooCommerce
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function() {
    echo '<main class="bp-main-content"><div class="bp-container">';
});

add_action('woocommerce_after_main_content', function() {
    echo '</div></main>';
});

// ===== WISHLIST (Favoritos) con persistencia =====
// Obtener wishlist del usuario logueado (desde user_meta)
function bp_get_wishlist() {
    $user_id = get_current_user_id();
    if ($user_id) {
        $wishlist = get_user_meta($user_id, 'bp_wishlist', true);
        return is_array($wishlist) ? $wishlist : [];
    }
    return [];
}

// Cupón colapsible entre total y métodos de pago
// Se llama directo desde form-checkout.php (no via hook)
function bp_checkout_coupon_form() {
    if (wc_coupons_enabled()) {
        ?>
        <div class="bp-checkout-coupon-wrap">
            <button type="button" class="bp-coupon-toggle">
                <i class="fas fa-ticket-alt"></i> ¿Tienes un cupón de descuento?
                <i class="fas fa-chevron-down bp-coupon-arrow"></i>
            </button>
            <div class="bp-coupon-body" style="display:none;">
                <div class="bp-coupon-form">
                    <input type="text" name="coupon_code" class="bp-coupon-input" placeholder="Código del cupón" id="coupon_code" value="" />
                    <button type="button" class="bp-coupon-apply" name="apply_coupon" value="Aplicar">Aplicar</button>
                </div>
            </div>
        </div>
        <?php
    }
}

// Endpoint "favoritos" en Mi Cuenta
add_action('init', function() {
    add_rewrite_endpoint('favoritos', EP_ROOT | EP_PAGES);
});
add_filter('woocommerce_account_menu_items', function($items) {
    $items['favoritos'] = 'Mis Favoritos';
    return $items;
});
add_action('woocommerce_account_favoritos_endpoint', function() {
    $wishlist = bp_get_wishlist();
    if (empty($wishlist)) {
        echo '<p>No tienes productos favoritos aún.</p>';
        return;
    }
    echo '<div class="bp-products-grid">';
    foreach ($wishlist as $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $city = get_field('localidad', $product_id) ?: get_post_meta($product_id, 'localidad', true);
            $nombre_establecimiento = get_field('nombre_establecimiento', $product_id) ?: get_post_meta($product_id, 'nombre_establecimiento', true);
            echo '<div class="bp-product-card">';
            echo '<div class="bp-product-image-wrap"><a href="' . get_permalink($product_id) . '">' . $product->get_image('medium_large') . '</a></div>';
            echo '<div class="bp-product-info">';
            echo '<h3 class="bp-product-title"><a href="' . get_permalink($product_id) . '">' . esc_html($nombre_establecimiento ?: $product->get_title()) . '</a></h3>';
            echo '<div class="bp-product-bottom"><div class="bp-product-price">' . $product->get_price_html() . '</div></div>';
            echo '</div></div>';
        }
    }
    echo '</div>';
});
// Refresh rewrite rules on theme switch
add_action('after_switch_theme', function() { flush_rewrite_rules(); });
add_action('wp_ajax_bp_toggle_wishlist', 'bp_toggle_wishlist');
function bp_toggle_wishlist() {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if (!$product_id) wp_die('0');
    
    $wishlist = bp_get_wishlist();
    $index = array_search($product_id, $wishlist);
    
    if ($index !== false) {
        unset($wishlist[$index]);
    } else {
        $wishlist[] = $product_id;
    }
    
    update_user_meta(get_current_user_id(), 'bp_wishlist', array_values($wishlist));
    wp_send_json(['wishlist' => array_values($wishlist)]);
}

// Forzar el template My Account de WooCommerce para la página de mi cuenta
// La página oficial (sin shortcode) usa el template app personalizado
add_filter('template_include', function($template) {
    if (is_account_page()) {
        $tpl = locate_template('woocommerce/myaccount/my-account.php');
        if ($tpl) return $tpl;
    }
    return $template;
});

// ============================================================
// FORMULARIOS: Contacto / Promociona tu negocio / Recibir ofertas
// ============================================================

// CONFIGURACIÓN SMTP BREVO
// ⚠️ LAS CREDENCIALES SE DEFINEN EN wp-config.php
// Añade esto a tu wp-config.php:
//
//   define('BP_BREVO_USER', 'tu_usuario_brevo@smtp-brevo.com');
//   define('BP_BREVO_PASS', 'tu_smtp_key_brevo');
//   define('BP_BREVO_FROM', 'info@bonospremium.com');
//
// El host/puerto por defecto apuntan a Brevo y pueden sobreescribirse igualmente.

if (!defined('BP_BREVO_HOST')) define('BP_BREVO_HOST', 'smtp-relay.brevo.com');
if (!defined('BP_BREVO_PORT')) define('BP_BREVO_PORT', 587);
if (!defined('BP_BREVO_USER')) define('BP_BREVO_USER', '');
if (!defined('BP_BREVO_PASS')) define('BP_BREVO_PASS', '');
if (!defined('BP_BREVO_FROM')) define('BP_BREVO_FROM', 'info@bonospremium.com');

// Configuración de cada formulario: email destino CONFIGURABLE
$bp_forms_config = [
    'contacto' => [
        'to'      => apply_filters('bp_form_contacto_to', 'info@bonospremium.com'),
        'subject' => '📩 Nuevo mensaje de contacto - BonosPremium',
    ],
    'promociona' => [
        'to'      => apply_filters('bp_form_promociona_to', 'info@bonospremium.com'),
        'subject' => '🏪 Promociona tu negocio - BonosPremium',
    ],
    'ofertas' => [
        'to'      => apply_filters('bp_form_ofertas_to', 'info@bonospremium.com'),
        'subject' => '🎁 Solicitud de recibir ofertas - BonosPremium',
    ],
];
// Filtro para sobreescribir todos los destinos desde child theme / snippet
function bp_forms_config() {
    return apply_filters('bp_forms_config', $GLOBALS['bp_forms_config']);
}

// ============================================================
// RECAPTCHA v3 — protege los formularios de spam
// Las keys se definen en wp-config.php:
//
//   define('BP_RECAPTCHA_SITE_KEY', 'TU_SITE_KEY_V3');
//   define('BP_RECAPTCHA_SECRET_KEY', 'TU_SECRET_KEY_V3');
//
// Puedes obtenerlas en: https://www.google.com/recaptcha/admin/create
// (Tipo: reCAPTCHA v3)

if (!defined('BP_RECAPTCHA_SITE_KEY'))    define('BP_RECAPTCHA_SITE_KEY', '');
if (!defined('BP_RECAPTCHA_SECRET_KEY'))  define('BP_RECAPTCHA_SECRET_KEY', '');

// Cargar script de reCAPTCHA v3 + token en los formularios
add_action('wp_enqueue_scripts', function() {
    if (empty(BP_RECAPTCHA_SITE_KEY)) return;
    wp_enqueue_script('bp-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . BP_RECAPTCHA_SITE_KEY, [], null, true);
});

// Añadir token hidden a cada formulario via JS (se rellena al cargar)
add_action('wp_footer', function() {
    if (empty(BP_RECAPTCHA_SITE_KEY)) return;
    ?>
    <script>
    jQuery(function($) {
        if (typeof grecaptcha === 'undefined' || typeof grecaptcha.ready !== 'function') return;
        grecaptcha.ready(function() {
            function fillCaptcha() {
                $('.bp-form').each(function() {
                    var $form = $(this);
                    if ($form.find('input[name="g-recaptcha-response"]').length) return;
                    grecaptcha.execute('<?php echo esc_js(BP_RECAPTCHA_SITE_KEY); ?>', {action: 'submit'}).then(function(token) {
                        if (!$form.find('input[name="g-recaptcha-response"]').length) {
                            $('<input>').attr({type: 'hidden', name: 'g-recaptcha-response', value: token}).appendTo($form);
                        } else {
                            $form.find('input[name="g-recaptcha-response"]').val(token);
                        }
                    });
                });
            }
            fillCaptcha();
            // Regenerar token si ha pasado tiempo (cada 100s)
            setInterval(fillCaptcha, 100000);
        });
    });
    </script>
    <?php
});

// Validar reCAPTCHA en el servidor al procesar el formulario
function bp_verify_recaptcha() {
    if (empty(BP_RECAPTCHA_SECRET_KEY)) return true; // no configurado, se permite

    $token = $_POST['g-recaptcha-response'] ?? '';
    if (empty($token)) return false;

    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => BP_RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);

    if (is_wp_error($response)) return false;
    $result = json_decode(wp_remote_retrieve_body($response), true);
    // score mínimo aceptable 0.5 (ajustable)
    return !empty($result['success']) && ($result['score'] ?? 0) >= apply_filters('bp_recaptcha_min_score', 0.5);
}

// Configurar PHPMailer para SMTP Brevo (solo si hay credenciales definidas)
add_action('phpmailer_init', function($phpmailer) {
    if (empty(BP_BREVO_USER) || empty(BP_BREVO_PASS)) return; // credenciales aún no configuradas
    $phpmailer->isSMTP();
    $phpmailer->Host       = BP_BREVO_HOST;
    $phpmailer->Port       = BP_BREVO_PORT;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = BP_BREVO_USER;
    $phpmailer->Password   = BP_BREVO_PASS;
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->From       = BP_BREVO_FROM;
    $phpmailer->FromName   = 'BonosPremium';
});

// Procesar envíos de formularios
add_action('init', function() {
    if (empty($_POST['bp_form_submit'])) return;

    $form = sanitize_key($_POST['bp_form_submit']);
    $config = bp_forms_config();
    if (!isset($config[$form])) return;

    // Nonce
    if (!wp_verify_nonce($_POST['bp_form_nonce'] ?? '', 'bp_form_' . $form)) {
        wp_die('Error de seguridad. Recarga la página e inténtalo de nuevo.');
    }

    // Verificar reCAPTCHA v3
    if (!bp_verify_recaptcha()) {
        wp_die('Error de verificación anti-spam. Recarga la página e inténtalo de nuevo.');
    }

    $fields = [
        'contacto'   => ['nombre', 'email', 'telefono', 'mensaje'],
        'promociona' => ['nombre', 'email', 'telefono', 'negocio', 'web', 'mensaje'],
        'ofertas'    => ['nombre', 'email', 'ciudad'],
    ];

    $data = [];
    foreach (($fields[$form] ?? []) as $f) {
        $data[$f] = sanitize_text_field(wp_unslash($_POST[$f] ?? ''));
    }

    // Validar email
    if (!is_email($data['email'] ?? '')) {
        wp_safe_redirect(add_query_arg('bp_form', $form, wp_get_referer() ?: home_url()) . '#bp-form-' . $form);
        exit;
    }

    // Construir cuerpo del correo
    $labels = [
        'nombre'   => 'Nombre',
        'email'    => 'Email',
        'telefono' => 'Teléfono',
        'mensaje'  => 'Mensaje',
        'negocio'  => 'Nombre del negocio',
        'web'      => 'Web / RRSS',
        'ciudad'   => 'Ciudad',
    ];
    $body = "Formulario: {$config[$form]['subject']}\n\n";
    foreach ($data as $k => $v) {
        $body .= ($labels[$k] ?? ucfirst($k)) . ": " . $v . "\n";
    }

    $headers = ['Reply-To: ' . $data['email']];

    wp_mail($config[$form]['to'], $config[$form]['subject'], $body, $headers);

    wp_safe_redirect(add_query_arg('bp_form', $form, wp_get_referer() ?: home_url()) . '#bp-form-' . $form . '&bp_ok=1');
    exit;
});

// Mostrar aviso de éxito
function bp_form_success($form) {
    if (isset($_GET['bp_ok']) && isset($_GET['bp_form']) && $_GET['bp_form'] === $form) {
        echo '<div class="bp-form-success">✅ ¡Gracias! Tu mensaje se ha enviado correctamente.</div>';
    }
}

// Campos comunes reutilizables
function bp_form_field($type, $name, $label, $required = true, $extra = '') {
    printf(
        '<p class="bp-form-row"><label for="%1$s">%2$s %3$s</label><input type="%4$s" name="%1$s" id="%1$s" placeholder="%2$s" %5$s /></p>',
        esc_attr($name),
        esc_html($label),
        $required ? '<span class="bp-form-required">*</span>' : '<span class="bp-form-opt">(opcional)</span>',
        esc_attr($type),
        $required ? 'required' : '',
        $extra
    );
}

