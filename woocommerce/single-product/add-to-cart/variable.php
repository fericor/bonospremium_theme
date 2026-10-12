<?php
/**
 * Variable product add to cart
 *
 * OVERRIDE BonosPremium LZ (25/08): rediseñado el variations_form.
 * - Sin label del atributo (se muestra como placeholder en el select)
 * - Sin botón "Limpiar"
 * - Selector a ancho completo + botón comprar (móvil full-width)
 *
 * Base: WooCommerce 11.0.1 (templates/single-product/add-to-cart/variable.php)
 *
 * @package WooCommerce\Templates
 * @version 10.9.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<div class="bp-variations-selector variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
					wc_dropdown_variation_attribute_options(
						array(
							'options'   => $options,
							'attribute' => $attribute_name,
							'product'   => $product,
							'class'     => 'bp-variation-select',
						)
					);
				?>
			<?php endforeach; ?>
			<?php
			// Reset variation ELIMINADO (31/08): el usuario pidió quitar el enlace "Limpiar".
			// El JS de WooCommerce ($form.find('.reset_variations')) tolera su ausencia sin romper
			// (devuelve vacío y no dispara errores); el reset_variations_alert de accesibilidad
			// se mantiene abajo.
			?>
		</div>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php
		// Reset snapshot for cases where a theme/plugin loads the variation form later, like quick-view modals.
		if ( \Automattic\WooCommerce\Internal\VariationGallery\Package::is_enabled() ) :
			?>
			<script type="text/template" class="wc-product-gallery-default-template"><?php echo wc_get_product_gallery_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
			<?php
		endif;
		?>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
