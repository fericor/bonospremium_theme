<?php
/**
 * Footer template
 */
?>
<!-- Wave shape at top -->
<div class="bp-footer-wave">
    <svg viewBox="0 0 1440 100" preserveAspectRatio="none" class="bp-wave-svg">
        <path d="M0,20 C180,70 360,10 540,30 C720,50 900,0 1080,20 C1260,40 1440,10 1440,10 L1440,100 L0,100 Z" fill="var(--bp-primary)" opacity="0.35"></path>
        <path d="M0,40 C320,100 640,0 960,50 C1280,100 1440,20 1440,20 L1440,100 L0,100 Z" fill="var(--bp-primary)" opacity="0.06"></path>
        <path d="M0,55 C240,90 560,10 960,55 C1280,100 1440,40 1440,40 L1440,100 L0,100 Z" fill="var(--bp-primary)" opacity="0.12"></path>
        <path d="M0,70 C180,85 480,20 960,70 C1280,100 1440,60 1440,60 L1440,100 L0,100 Z" fill="var(--bp-primary)" opacity="0.2"></path>
    </svg>
</div>
<footer class="bp-footer">
    <div class="bp-footer-widgets">
        <div class="bp-container bp-footer-grid">
            <div class="bp-footer-col">
                <h4 class="bp-footer-title">Sobre nosotros</h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer-about',
                    'container'      => false,
                    'menu_class'     => 'bp-footer-menu',
                    'fallback_cb'    => function() {
                        echo '<ul class="bp-footer-menu">';
                        echo '<li><a href="' . home_url('/como-funciona') . '">¿Cómo funciona BonosPremium?</a></li>';
                        echo '<li><a href="' . home_url('/aviso-legal') . '">Aviso legal</a></li>';
                        echo '<li><a href="' . home_url('/promociona-tu-negocio') . '">¡Promociona tu negocio!</a></li>';
                        echo '<li><a href="' . home_url('/politica-de-cookies') . '">Uso de cookies</a></li>';
                        echo '<li><a href="' . home_url('/politica-de-privacidad') . '">Política de Privacidad</a></li>';
                        echo '<li><a href="' . home_url('/condiciones-generales') . '">Condiciones Generales</a></li>';
                        echo '</ul>';
                    },
                    'depth' => 1,
                ]);
                ?>
            </div>
            <div class="bp-footer-col">
                <h4 class="bp-footer-title">Mi cuenta</h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer-account',
                    'container'      => false,
                    'menu_class'     => 'bp-footer-menu',
                    'fallback_cb'    => function() {
                        echo '<ul class="bp-footer-menu">';
                        echo '<li><a href="' . wc_get_page_permalink('myaccount') . '">Mi cuenta</a></li>';
                        echo '<li><a href="' . wc_get_page_permalink('myaccount') . 'favoritos/">Mis favoritos</a></li>';
                        echo '<li><a href="' . wc_get_page_permalink('myaccount') . 'orders/">Mis Bonos</a></li>';
                        echo '<li><a href="' . wc_get_page_permalink('myaccount') . 'edit-address/">Direcciones</a></li>';
                        echo '<li><a href="' . wc_get_page_permalink('myaccount') . 'edit-account/">Detalles de la cuenta</a></li>';
                        echo '</ul>';
                    },
                    'depth' => 1,
                ]);
                ?>
            </div>
            <div class="bp-footer-col">
                <h4 class="bp-footer-title">Ofertas</h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer-offers',
                    'container'      => false,
                    'menu_class'     => 'bp-footer-menu',
                    'fallback_cb'    => function() {
                        $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 8]);
                        if (!empty($cats)) {
                            echo '<ul class="bp-footer-menu">';
                            foreach ($cats as $cat) {
                                echo '<li><a href="' . get_term_link($cat) . '">' . $cat->name . '</a></li>';
                            }
                            echo '</ul>';
                        }
                    },
                    'depth' => 1,
                ]);
                ?>
            </div>
            <div class="bp-footer-col">
                <h4 class="bp-footer-title">Contáctanos</h4>
                <div class="bp-footer-contact">
                    <p><a href="<?php echo home_url('/contacto'); ?>"><i class="fas fa-envelope"></i> Contacta con nosotros</a></p>
                    <div class="bp-footer-social">
                        <a href="https://facebook.com/bonospremiumlanzarote" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com/bonospremiumlanzarote" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <a href="https://wa.me/34600000000" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bp-footer-bottom">
        <div class="bp-container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<?php get_template_part('cookies-consent'); ?>

<!-- Modal mapa de bonos (global 03/09) — pantalla completa con todos los markers.
     Antes vivía solo en single-product.php; movido al footer para poder abrirse
     desde el menú de WP (enlace con clase bp-open-map) en cualquier página. -->
<div class="bp-map-modal" id="bp-map-modal" role="dialog" aria-modal="true" aria-label="Mapa de bonos">
    <button type="button" class="bp-map-modal-close" aria-label="Cerrar mapa">&times;</button>
    <div class="bp-map-modal-body">
        <div class="bp-map-rango">
            <span class="bp-map-rango-ico" aria-hidden="true"><i class="fas fa-location-crosshairs"></i></span>
            <label class="bp-map-rango-label" for="bp-map-rango-input">Radio</label>
            <input type="range" id="bp-map-rango-input" class="bp-map-rango-input" min="100" max="500" step="25" value="100" aria-label="Radio de búsqueda en kilómetros" />
            <span class="bp-map-rango-val" id="bp-map-rango-val">100 km</span>
        </div>
        <div id="bp-map-bonos" class="bp-map-bonos"></div>
        <div id="bp-map-vacio" class="bp-map-vacio" style="display:none;">
            <div class="bp-map-vacio-card">
                <button type="button" class="bp-map-vacio-cerrar" id="bp-map-vacio-cerrar" aria-label="Cerrar aviso">×</button>
                <div class="bp-map-vacio-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h4 class="bp-map-vacio-titulo">Sin bonos en este radio</h4>
                <p class="bp-map-vacio-desc">No hay bonos a <strong id="bp-map-vacio-km">100 km</strong> del centro del mapa.<br />Cierra este aviso para ver todos los bonos del mapa.</p>
                <button type="button" class="bp-map-vacio-btn" id="bp-map-vacio-btn">Ampliar radio a 500 km</button>
            </div>
        </div>
        <div id="bp-map-tarjeta" class="bp-map-tarjeta" style="display:none;"></div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
