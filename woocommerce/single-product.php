<?php
/**
 * Custom single-product.php for BonosPremium Theme
 */
get_header(); ?>

<main class="bp-main-content">
    <div class="bp-container">
        <?php while (have_posts()) : the_post();
            global $product;
            $city = get_field('localidad') ?: get_post_meta(get_the_ID(), 'localidad', true);
            $nombre_establecimiento = get_field('nombre_establecimiento') ?: get_post_meta(get_the_ID(), 'nombre_establecimiento', true);
            $excerpt = get_the_excerpt();
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price() ?: $regular_price;
        ?>
        <div class="bp-single-product">
            <div class="bp-single-gallery">
                <div class="bp-slider">
                    <div class="bp-slider-track">
                        <?php
                        // Main product image
                        echo '<div class="bp-slide">' . $product->get_image('full') . '</div>';
                        // Gallery images
                        $galleries = $product->get_gallery_image_ids();
                        if (!empty($galleries)) {
                            foreach ($galleries as $gid) {
                                echo '<div class="bp-slide">' . wp_get_attachment_image($gid, 'full') . '</div>';
                            }
                        }
                        ?>
                    </div>
                    <div class="bp-slider-dots"></div>
                </div>
            </div>
            <div class="bp-single-summary">
                <div class="bp-single-categories">
                    <?php echo wc_get_product_category_list($product->get_id(), ', '); ?>
                </div>
                <h1 class="bp-single-title">
                    <?php echo esc_html($nombre_establecimiento ?: get_the_title()); ?>
                </h1>
                <div class="bp-single-price">
                    <?php if ($regular_price && $regular_price != $sale_price) : ?>
                        <span class="bp-price-original"><?php echo wc_price($regular_price); ?></span>
                    <?php endif; ?>
                    <span class="bp-price-sale"><?php echo wc_price($sale_price); ?></span>
                </div>
                
                <h3 class="bp-section-title bp-color-primary">Tu experiencia</h3>
                <?php if (!empty($excerpt)) : ?>
                    <div class="bp-single-desc"><?php echo apply_filters('the_excerpt', $excerpt); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($city)) : ?>
                    <p class="bp-single-city"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($city); ?></p>
                <?php endif; ?>
                
                <?php
                $direccion = get_field('direccion') ?: get_post_meta(get_the_ID(), 'direccion', true);
                $telefono = get_field('telefono') ?: get_post_meta(get_the_ID(), 'telefono', true);
                $condiciones = get_field('condiciones_generales') ?: get_post_meta(get_the_ID(), 'condiciones_generales', true);
                $mapa = get_field('mapa') ?: get_post_meta(get_the_ID(), 'mapa', true);
                ?>
                
                <?php if (!empty($direccion) || !empty($telefono) || !empty($city)) : ?>
                <div class="bp-contact-info">
                    <?php if (!empty($direccion)) : ?>
                        <p class="bp-contact-item"><i class="fas fa-map-pin"></i> <?php echo esc_html($direccion); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($telefono)) : ?>
                        <p class="bp-contact-item"><i class="fas fa-phone"></i> <a href="tel:<?php echo esc_attr($telefono); ?>"><?php echo esc_html($telefono); ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($mapa['lat']) && !empty($mapa['lng'])) : ?>
                        <p class="bp-contact-item">
                            <i class="fas fa-directions"></i> 
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($mapa['lat']); ?>,<?php echo esc_attr($mapa['lng']); ?>" target="_blank" rel="noopener">
                                ¿Cómo llegar hasta allí?
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($condiciones)) : ?>
                <h3 class="bp-section-title bp-color-primary">Condiciones</h3>
                <div class="bp-condiciones"><?php echo wp_kses_post($condiciones); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($mapa['lat']) && !empty($mapa['lng'])) : ?>
                <div class="bp-map-wrap">
                    <iframe 
                        width="100%" 
                        height="250" 
                        frameborder="0" 
                        style="border:0; border-radius:12px;" 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo esc_attr($mapa['lng'] - 0.01); ?>%2C<?php echo esc_attr($mapa['lat'] - 0.01); ?>%2C<?php echo esc_attr($mapa['lng'] + 0.01); ?>%2C<?php echo esc_attr($mapa['lat'] + 0.01); ?>&amp;layer=mapnik&amp;marker=<?php echo esc_attr($mapa['lat']); ?>%2C<?php echo esc_attr($mapa['lng']); ?>"
                        allowfullscreen>
                    </iframe>
                </div>
                <?php endif; ?>
                <div class="bp-single-cart">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
                <div class="bp-single-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div class="bp-cart-spacer"></div>
</main>

<?php get_footer(); ?>
