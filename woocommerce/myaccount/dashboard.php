<?php
/**
 * My Account Dashboard — HTML personalizado BonosPremium LZ
 * Estilo app: saludo + tarjetas de resumen + pedido reciente + direcciones.
 * Reemplaza al dashboard.php por defecto de WooCommerce.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0 (estructura base) — personalizado BonosPremium 09/09
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user_id = get_current_user_id();
$nombre          = $current_user ? $current_user->display_name : '';

// Pedidos del usuario (último pedido para el resumen)
$ultimo_pedido = null;
$pedidos_ids   = wc_get_orders( array(
	'customer' => $current_user_id,
	'limit'    => 1,
	'status'   => array_keys( wc_get_order_statuses() ),
	'return'   => 'ids',
) );
if ( ! empty( $pedidos_ids ) ) {
	$ultimo_pedido = wc_get_order( $pedidos_ids[0] );
}
$total_pedidos = count( wc_get_orders( array(
	'customer' => $current_user_id,
	'limit'    => -1,
	'status'   => array_keys( wc_get_order_statuses() ),
	'return'   => 'ids',
) ) );

// Crédito BonosPremium (wallet)
$wallet = function_exists( 'bp_get_user_wallet' ) ? bp_get_user_wallet( $current_user_id ) : array( 'saldo' => 0 );
$saldo_credito = isset( $wallet['saldo'] ) ? $wallet['saldo'] : 0;

// Favoritos (smart-wishlist)
$wishlist_count = 0;
$wishlist_ids = get_user_meta( $current_user_id, 'wlfmc_wishlist', true );
if ( is_array( $wishlist_ids ) ) {
	$wishlist_count = count( $wishlist_ids );
}
?>
<div class="bp-dash-hello">
	<h2>¡Hola, <?php echo esc_html( $nombre ); ?>!</h2>
	<p>Esto es un resumen de tu cuenta BonosPremium.</p>
</div>

<div class="bp-dash-grid">
	<!-- Saldo de crédito -->
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'credito-bonospremium' ) ); ?>" class="bp-dash-card">
		<span class="bp-dash-card-info">
			<span class="bp-dash-card-value"><?php echo esc_html( number_format( (float) $saldo_credito, 2 ) ); ?> €</span>
			<span class="bp-dash-card-label">Crédito disponible</span>
		</span>
	</a>

	<!-- Pedidos -->
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="bp-dash-card">
		<span class="bp-dash-card-info">
			<span class="bp-dash-card-value"><?php echo (int) $total_pedidos; ?></span>
			<span class="bp-dash-card-label">Pedidos</span>
		</span>
	</a>

	<!-- Favoritos -->
	<a href="<?php echo esc_url( home_url( '/favoritos/' ) ); ?>" class="bp-dash-card">
		<span class="bp-dash-card-info">
			<span class="bp-dash-card-value"><?php echo (int) $wishlist_count; ?></span>
			<span class="bp-dash-card-label">Favoritos</span>
		</span>
	</a>

	<!-- Direcciones -->
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="bp-dash-card">
		<span class="bp-dash-card-info">
			<span class="bp-dash-card-value"><?php echo esc_html( number_format_i18n( 2 ) ); ?></span>
			<span class="bp-dash-card-label">Direcciones</span>
		</span>
	</a>
</div>

<?php if ( $ultimo_pedido ) : ?>
	<div class="bp-dash-ultimo-pedido">
		<h3>Último pedido</h3>
		<div class="bp-dash-ultimo-pedido-row">
			<span class="bp-dash-ultimo-pedido-num"><?php echo esc_html( $ultimo_pedido->get_order_number() ); ?></span>
			<span class="bp-dash-ultimo-pedido-fecha"><?php echo esc_html( wc_format_datetime( $ultimo_pedido->get_date_created() ) ); ?></span>
			<span class="bp-dash-ultimo-pedido-total"><?php echo wp_kses_post( $ultimo_pedido->get_formatted_order_total() ); ?></span>
			<a class="bp-dash-ultimo-pedido-ver" href="<?php echo esc_url( $ultimo_pedido->get_view_order_url() ); ?>">Ver</a>
		</div>
	</div>
<?php endif; ?>

<?php
/**
 * Hook del dashboard de Mi Cuenta (mantenido por compatibilidad).
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
