<?php
/**
 * Custom Cart page for BonosPremium
 * Carrito sin quantity (producto único, siempre 1)
 */
get_header(); ?>

<main class="bp-main-content">
    <div class="bp-container">
        <h1 class="bp-page-title">Carrito</h1>

        <?php if (WC()->cart && !WC()->cart->is_empty()) : ?>
            <form class="bp-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                <div class="bp-cart-grid">
                    <!-- Lista de productos -->
                    <div class="bp-cart-items">
                        <table class="bp-cart-table">
                            <thead>
                                <tr>
                                    <th class="bp-col-product">Producto</th>
                                    <th class="bp-col-price bp-text-right">Precio</th>
                                    <th class="bp-col-remove"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                    if (!$_product || !$_product->exists()) continue;
                                    $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail'), $cart_item, $cart_item_key);
                                ?>
                                <tr>
                                    <td class="bp-col-product">
                                        <div class="bp-cart-product">
                                            <div class="bp-cart-thumb">
                                                <?php echo $thumbnail; ?>
                                            </div>
                                            <div class="bp-cart-info">
                                                <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>" class="bp-cart-name">
                                                    <?php echo $product_name; ?>
                                                </a>
                                                <!-- Quantity oculto, siempre 1 -->
                                                <input type="hidden" name="cart[<?php echo $cart_item_key; ?>][qty]" value="1" />
                                                <span class="bp-cart-qty-label">Cant: 1</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="bp-col-price bp-text-right">
                                        <?php echo WC()->cart->get_product_subtotal($_product, 1); ?>
                                    </td>
                                    <td class="bp-col-remove">
                                        <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" class="bp-remove-item" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="bp-cart-actions">
                            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="bp-btn-secondary">
                                <i class="fas fa-arrow-left"></i> Seguir comprando
                            </a>
                            <button type="submit" class="bp-btn-secondary" name="update_cart" value="Actualizar carrito" disabled style="display:none;">
                                <i class="fas fa-sync"></i> Actualizar
                            </button>
                        </div>
                    </div>

                    <!-- Sidebar: Total + checkout -->
                    <div class="bp-cart-sidebar">
                        <div class="bp-cart-card">
                            <h3>Resumen</h3>
                            <div class="bp-cart-totals">
                                <div class="bp-total-row">
                                    <span>Subtotal</span>
                                    <span><?php wc_cart_totals_subtotal_html(); ?></span>
                                </div>
                                <?php if (WC()->cart->get_cart_contents_count() > 1) : ?>
                                <div class="bp-total-row">
                                    <span>Productos</span>
                                    <span><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="bp-total-row bp-total-final">
                                    <span>Total</span>
                                    <span><?php wc_cart_totals_order_total_html(); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="bp-checkout-btn">
                                <i class="fas fa-lock"></i> Finalizar compra
                            </a>
                            <?php do_action('woocommerce_proceed_to_checkout'); ?>
                        </div>
                    </div>
                </div>
            </form>
        <?php else : ?>
            <div class="bp-cart-empty">
                <div class="bp-empty-icon"><i class="fas fa-shopping-bag"></i></div>
                <h2>Tu carrito está vacío</h2>
                <p>Explora nuestros productos y encuentra tu experiencia ideal.</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="bp-btn-primary">Ver productos</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.bp-cart-grid {
    display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;
}
.bp-cart-table {
    width: 100%; border-collapse: collapse; background: #fff;
    border-radius: 16px; overflow: hidden;
    border: 1px solid #e5e7eb;
}
.bp-cart-table th {
    text-align: left; padding: 16px 20px; font-size: 12px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; color: #9ca3af;
    background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.bp-cart-table td { padding: 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.bp-cart-table tbody tr:last-child td { border: none; }
.bp-cart-product { display: flex; align-items: center; gap: 16px; }
.bp-cart-thumb { width: 72px; height: 72px; border-radius: 12px; overflow: hidden; flex-shrink: 0; }
.bp-cart-thumb img { width: 100%; height: 100%; object-fit: cover; }
.bp-cart-name { font-size: 15px; font-weight: 600; color: #1a1a2e; text-decoration: none; }
.bp-cart-name:hover { color: #53abc1; }
.bp-cart-qty-label { display: block; font-size: 13px; color: #9ca3af; margin-top: 4px; }
.bp-col-price { font-size: 15px; font-weight: 600; color: #1a1a2e; white-space: nowrap; }
.bp-col-remove { width: 40px; text-align: center; }
.bp-remove-item { color: #d1d5db; font-size: 16px; transition: color .2s; }
.bp-remove-item:hover { color: #ef4444; }
.bp-cart-actions { display: flex; gap: 12px; margin-top: 16px; }
.bp-cart-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px;
    position: sticky; top: 100px;
}
.bp-cart-card h3 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0 0 16px; }
.bp-cart-totals { margin-bottom: 20px; }
.bp-total-row {
    display: flex; justify-content: space-between; padding: 10px 0;
    border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #6b7280;
}
.bp-total-final { border: none; border-top: 2px solid #e5e7eb; padding-top: 14px; margin-top: 4px; font-weight: 700; color: #1a1a2e; font-size: 16px; }
.bp-checkout-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 14px; background: linear-gradient(135deg, #53abc1, #3d8fa3);
    color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 600;
    text-decoration: none; cursor: pointer; transition: opacity .2s;
}
.bp-checkout-btn:hover { opacity: .9; color: #fff; }
.bp-text-right { text-align: right; }
.bp-page-title { font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 0 0 24px; }
.bp-cart-empty { text-align: center; padding: 60px 20px; }
.bp-empty-icon { font-size: 64px; color: #d1d5db; margin-bottom: 16px; }
.bp-cart-empty h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; }
.bp-cart-empty p { color: #6b7280; margin: 0 0 24px; }
.bp-btn-primary, .bp-btn-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 600;
    text-decoration: none; transition: all .2s;
}
.bp-btn-primary { background: linear-gradient(135deg, #53abc1, #3d8fa3); color: #fff; }
.bp-btn-primary:hover { opacity: .9; color: #fff; }
.bp-btn-secondary { background: #f3f4f6; color: #374151; border: none; cursor: pointer; }
.bp-btn-secondary:hover { background: #e5e7eb; color: #374151; }

@media (max-width: 768px) {
    .bp-cart-grid { grid-template-columns: 1fr; }
    .bp-cart-table th, .bp-cart-table td { padding: 12px; }
    .bp-cart-thumb { width: 56px; height: 56px; }
}
</style>

<?php get_footer(); ?>
