<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account-dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<p class="hello-customer">
	<?php
		echo sprintf( esc_attr__( 'Hello %s%s%s %s(not %2$s? %sSign out%s)%s', 'sober' ), '<strong>', esc_html( $current_user->display_name ), '</strong>', '<span class="not-admin">', '<a href="' . esc_url( wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) ) ) . '">', '</a>', '</span>' );
	?>
</p>

<p>
	<?php
		echo sprintf( esc_attr__( 'From your account dashboard you can view your %1$srecent orders%2$s, manage your %3$sshipping and billing addresses%2$s and %4$sedit your password and account details%2$s.', 'sober' ), '<a href="' . esc_url( wc_get_endpoint_url( 'orders' ) ) . '">', '</a>', '<a href="' . esc_url( wc_get_endpoint_url( 'edit-address' ) ) . '">', '<a href="' . esc_url( wc_get_endpoint_url( 'edit-account' ) ) . '">' );
	?>
</p>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );
?>
<p>
    <a id="send_email">sendmail</a></br>
    <a id="share_face_book">share to facebook</a></br>
    <a id="share_twitter">share to twitter</a></br>
    <a id="share_google">share to google+</a></br>
</p>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script>


    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));



    window.fbAsyncInit = function() {
        FB.init({
            appId            : '1955847301339546',
            autoLogAppEvents : true,
            xfbml            : true,
            version          : 'v2.11'
        });

        $("#share_face_book").click(function(){
            console.log('$("#raf-message a").html():'+$("#raf-message a").html());
            FB.ui(
                {
                    method: 'share',
                    href: $("#raf-message a").html()
                }, function(response){});
        });
    };
    $(document).ready(function(){
       $("#send_email").click(function(){

           console.log('$("#raf-message a").html():'+$("#raf-message a").html());
           window.open('mailto:uncle.cyan@gmail.com?subject=spade.com&body='+$("#raf-message a").html());
       });
    });


</script>
<script>
    window.twttr = (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
        if (d.getElementById(id)) return t;
        js = d.createElement(s);
        js.id = id;
        js.src = "https://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js, fjs);

        t._e = [];
        t.ready = function(f) {
            t._e.push(f);
        };

        return t;
    }(document, "script", "twitter-wjs"));

</script>