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
	/*woocommerce_quantity_input( array(
		'min_value'   => apply_filters( 'woocommerce_quantity_input_min', method_exists( $product, 'get_min_purchase_quantity' ) ? $product->get_min_purchase_quantity() : 1, $product ),
		'max_value'   => apply_filters( 'woocommerce_quantity_input_max', method_exists( $product, 'get_max_purchase_quantity' ) ? $product->get_max_purchase_quantity() : ($product->backorders_allowed() ? '' : $product->get_stock_quantity()), $product ),
		'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : ( method_exists( $product, 'get_min_purchase_quantity' ) ? $product->get_min_purchase_quantity() : 1 ),
	) );*/
    ?>

    <button  type="submit" name="direct" class="single_add_to_cart_button price direct-purchase"><?php esc_html_e( 'direct buy　$'.$product->price*1.3, 'sober' ); ?></button>
    <?php

	/**
	 * @since 3.0.0.
	 */
	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>
	<button type="submit" name="group"  class="single_add_to_cart_button price friendeal">
        <?php esc_html_e( 'friendeal　$'.$product->price, 'sober' ); ?>
	</button>
    <!--<a href="<?php /*echo esc_url( wc_get_checkout_url() ); */?>" class="button checkout wc-forward">
        <span class="subtotal"><?php /*echo $product->get_min_purchase_quantity */?></span>
        <span></span>
    </a>-->
    <input type="hidden" name="direct" value="1" />
	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="1" />
    <input type="hidden" name="checkout_url" value="<?php echo esc_url( wc_get_checkout_url()) ?>"/>

    <script>
        jQuery("[name='group']").click(function(){
                jQuery("[name='direct']").val("0");
        });
    </script>
</div>