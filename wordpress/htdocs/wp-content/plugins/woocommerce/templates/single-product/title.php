<style>
    .wishlist,.product_title {display: inline-block;vertical-align: top;}
    .product_title{width:80%;}
    button.price{padding:6px 0;border-radius: 3px;}
    .direct-purchase{width:35%;background: #599bd5;}
    .direct-purchase:hover{background: #599bd5;}
    .friendeal{width:50%;background: #ed7d31;}
    .friendeal:hover{background: #ed7d31;}
</style>
<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author     WooThemes
 * @package    WooCommerce/Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

the_title( '<h1 class="product_title entry-title">', '</h1>' );

?>
<form class="cart wishlist">
    <?php
        if ( shortcode_exists( 'add_to_wishlist' ) ) {
            echo do_shortcode( '[add_to_wishlist]' );
        }
?>
</form>