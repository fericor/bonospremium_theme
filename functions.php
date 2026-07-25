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

// Forzar login visible en checkout y toggle suave
// Nota: el toggle del login y auto-dismiss de notices están ahora en assets/js/main.js
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

// Ocultar wishlist duplicado del plugin
add_action('wp_head', function() {
    echo '<style>.wlfmc-add-to-wishlist { display: none !important; }</style>';
});

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
    echo woocommerce_get_product_thumbnail();
    echo '</a>';
    echo '<a href="#" class="bp-wishlist-btn" data-product-id="' . get_the_ID() . '"><i class="far fa-heart"></i></a>';
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
