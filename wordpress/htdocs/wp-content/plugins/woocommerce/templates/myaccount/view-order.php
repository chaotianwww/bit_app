<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p>
    <?php
    $order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
    do_action( 'woocommerce_after_payment_share',['order_id'=> $order->get_order_number(),'item'=>$order_items]); ?>
<div class="woocommerce-share">
    <button id="share_on_time_line" class="share-link"><i class="icon-facebook"></i>share on timeline</button>
    <button id="share_to_whats_app" class="share-link"><i class="icon-whatsapp"></i>share to whatsapp</button>
    <button id="share_to_fb_msg" class="share-link"><i class="icon-fb-msg"></i>share to fb msg</button>
</div>
    <?php
	/* translators: 1: order number 2: order date 3: order status */
	printf(
		__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
		'<mark class="order-number">' . $order->get_order_number() . '</mark>',
		'<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>',
		'<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>'
	);
?></p>

<?php if ( $notes = $order->get_customer_order_notes() ) : ?>
	<h2><?php _e( 'Order updates', 'woocommerce' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
		<li class="woocommerce-OrderUpdate comment note">
			<div class="woocommerce-OrderUpdate-inner comment_container">
				<div class="woocommerce-OrderUpdate-text comment-text">
					<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
					<div class="woocommerce-OrderUpdate-description description">
						<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
					</div>
	  				<div class="clear"></div>
	  			</div>
				<div class="clear"></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>
<script>

    console.log("xxxxxxxxxxxxxxxxxxxxxxxx");
    window.fbAsyncInit = function() {
        FB.init({
            appId            : '1955847301339546',
            autoLogAppEvents : true,
            xfbml            : true,
            version          : 'v2.11'
        });
    };
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    jQuery(document).ready(function(){
        jQuery(".woocommerce-message").addClass("woocommerce-message-bgcolor");

        jQuery("#share_on_time_line").click(function(){
            console.log("share_on_time_line");
            FB.ui({method: 'share',
                href: jQuery("#raf-message a").html()
            }, function(response){});
        });

        jQuery("#share_to_whats_app").click(function(){
            console.log("share_to_whats_app");
            window.open("whatsapp://send??text="+jQuery("#raf-message a").html());
        });
        jQuery("#share_to_fb_msg").click(function(){
            console.log("share_to_fb_msg");
            window.open("fb-messenger://share?link="+encodeURIComponent(jQuery("#raf-message a").html())+ '&app_id=' + encodeURIComponent("1955847301339546"));
        });
        jQuery("#share_link_url").click(function(){
            document.querySelector('#input').select();
            document.execCommand('copy');
            alert("复制成功！");
        });


    });
</script>
<?php do_action( 'woocommerce_view_order', $order_id ); ?>
