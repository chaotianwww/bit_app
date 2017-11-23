<?php
/**
 * Single variation cart button
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button">
	<?php
	/**
	 * @since 3.0.0.
	 */
	do_action( 'woocommerce_before_add_to_cart_quantity' );
	woocommerce_quantity_input( array(
		'min_value'   => apply_filters( 'woocommerce_quantity_input_min', method_exists( $product, 'get_min_purchase_quantity' ) ? $product->get_min_purchase_quantity() : 1, $product ),
		'max_value'   => apply_filters( 'woocommerce_quantity_input_max', method_exists( $product, 'get_max_purchase_quantity' ) ? $product->get_max_purchase_quantity() : ($product->backorders_allowed() ? '' : $product->get_stock_quantity()), $product ),
		'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : ( method_exists( $product, 'get_min_purchase_quantity' ) ? $product->get_min_purchase_quantity() : 1 ),
	) );

	/**
	 * @since 3.0.0.
	 */
	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="submit" class="single_add_to_cart_button button alt">

        <?php esc_html_e( 'Checkout', 'sober' ); ?>
	</button>
    <!--<a href="<?php /*echo esc_url( wc_get_checkout_url() ); */?>" class="button checkout wc-forward">
        <span class="subtotal"><?php /*echo $product->get_min_purchase_quantity */?></span>
        <span></span>
    </a>-->
	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
    <input type="hidden" name="checkout_url" value="<?php echo esc_url( wc_get_checkout_url()); ?>"/>


</div>
