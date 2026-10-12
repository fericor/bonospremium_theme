<?php
/**
 * Header template
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php get_template_part('loader'); ?>
<?php wp_body_open(); ?>

<header class="bp-header">
    <div class="bp-header-top">
        <div class="bp-header-container bp-header-inner">
            <div class="bp-header-left">
                <?php if ( class_exists('WooCommerce') && (is_product() || is_checkout()) ) : ?>
                <button class="bp-back-button" onclick="window.history.back()" aria-label="Atrás">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <?php else : ?>
                <button class="bp-menu-toggle" aria-label="Menú">
                    <span class="bp-hamburger" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <?php endif; ?>
                <nav class="bp-user-nav">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'user-menu',
                        'container'      => false,
                        'menu_class'     => 'bp-user-nav-menu',
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ]);
                    ?>
                </nav>
            </div>

            <div class="bp-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php
                    $bp_logo_url = bp_get_setting('logo_url', '');
                    if ($bp_logo_url !== '') {
                        echo '<img src="' . esc_url($bp_logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="bp-logo-img" />';
                    } else {
                        echo '<img src="' . get_template_directory_uri() . '/assets/logo_rectangulo.png" alt="BonosPremium" class="bp-logo-img" />';
                    }
                    ?>
                </a>
            </div>

            <div class="bp-header-right">
                <div class="bp-header-search-desktop">
                    <?php if (class_exists('WooCommerce')): ?>
                        <form role="search" method="get" class="bp-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="search" placeholder="Buscar..." value="<?php echo get_search_query(); ?>" name="s" />
                            <input type="hidden" name="post_type" value="product" />
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php if (class_exists('WooCommerce')): ?>
                <!-- <button type="button" class="bp-header-action bp-open-map" title="Mapa de bonos" aria-label="Mapa de bonos">
                    <i class="fa-solid fa-map-location-dot"></i>
                </button> -->
				<button type="button" class="bp-header-action bp-open-map" title="Mapa de bonos" aria-label="Mapa de bonos">
 					<!-- <img class="icono-mapa" src="<?php echo get_template_directory_uri(); ?>/assets/icons/mapa_blanco.png" alt="Mi imagen"> -->
					
<svg xmlns="http://www.w3.org/2000/svg" width="30" height="32" viewBox="0 0 406 378" fill="#fff">
<g transform="translate(0,378) scale(.1,-.1)">
<path d="M2974 3240l-431-219-569 190c-557 186-570 190-639 186-66-4-94-15-509-204-334-151-445-206-460-227-21-27-21-34-24-1254-1-845 1-1235 8-1253 13-31 59-59 97-59 17 0 200 88 457 219l429 218 536-178c295-99 539-179 543-179 4 0-26 55-66 123l-72 122-414 141-415 142-3 1090-2 1091 497-168 498-168 3-342 2-341 61 45c34 25 79 55 100 67l39 22v273 273l350 175 350 175v-393-392l83-22c45-12 90-24 100-28 16-7 17 21 17 503 0 346-4 519-11 535-14 30-59 57-96 57-17 0-201-89-459-220zm-1734-1155v-1075l-350-175-350-175v1092 1093l347 157c191 87 348 158 350 158 1 0 2-484 2-1075z"/>
<path d="M3045 2124c-11-2-44-9-73-15-217-44-428-219-517-430-102-240-81-467 75-784 94-192 313-506 463-664 74-77 146-97 224-62 43 20 170 167 303 351 248 343 385 686 366 920-24 311-242 574-546 660-72 20-250 35-295 24zm285-226c231-85 372-299 357-544-14-232-155-506-461-891-45-56-83-102-86-102-11 0-201 249-284 372-101 152-206 358-236 467-63 226-20 420 126 566 66 66 172 126 266 150 81 21 237 12 318-18z"/>
<path d="M3063 1720c-100-21-187-94-236-198-27-57-31-78-31-147 0-65 5-91 26-135 34-75 96-136 176-173 57-27 78-31 147-31 65 0 91 5 135 26 75 34 136 96 173 176 27 57 31 78 31 147 0 97-28 166-93 233-79 81-219 125-328 102zm147-208c42-21 80-83 80-132 0-109-121-182-220-132-42 21-80 83-80 132 0 109 121 182 220 132z"/>
</g>
</svg>


                </button>
                <?php endif; ?>
                <button class="bp-header-action bp-search-toggle bp-search-mobile" title="Buscar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Search overlay -->
        <div class="bp-search-overlay">
            <div class="bp-search-overlay-inner">
                <?php if (class_exists('WooCommerce')): ?>
                    <form role="search" method="get" class="bp-search-form bp-search-form-overlay" action="<?php echo esc_url(home_url('/')); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                        <input type="search" placeholder="Buscar..." value="<?php echo get_search_query(); ?>" name="s" autofocus />
                        <input type="hidden" name="post_type" value="product" />
                        <button type="button" class="bp-search-close"><i class="fas fa-times"></i></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<nav class="bp-nav">
    <div class="bp-header-container">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'bp-nav-menu',
            'fallback_cb'    => 'bp_lz_fallback_menu',
            'depth'          => 1,
        ]);
        ?>
    </div>
</nav>

<?php
// Fallback menu si no hay menú asignado
function bp_lz_fallback_menu() {
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    if (!empty($categories)) {
        echo '<ul class="bp-nav-menu">';
        echo '<li><a href="' . get_permalink(wc_get_page_id('shop')) . '">Todo</a></li>';
        foreach ($categories as $cat) {
            echo '<li><a href="' . get_term_link($cat) . '">' . $cat->name . '</a></li>';
        }
        echo '</ul>';
    }
}
