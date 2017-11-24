<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
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
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
    <style>
        .the_champ_outer_login_container {
            margin: 0 auto;
            width: 400px;
            max-width: 100%;
            height: 50px;
        }

        ul.the_champ_login_ul li {
            float: none
        }

        .theChampLogin {
            width: 99%;
            height: 50px;
            line-height: 50px;
            color: #fff;
            font-style: inherit;
        }

        .theChampLogin .theChampLoginSvg {
            display: inline-block;
            margin-left: 15px;
            vertical-align: top;
            width: 10%;
        }

        .the_champ_login_hr {
            clear: both;
            margin: 50px auto;
            width: 400px;
            max-width: 100%;
            text-align: center;
            position: relative;
        }

        .the_champ_login_hr div {
            position: absolute;
            top: -10px;
            left: 50%;
            margin-left: -10px;
            width: 25px;
            height: 25px;
            line-height: 20px;
            border-radius: 25px;
            background: #fff;
            text-align: center;
            border: 1px solid #ccc;
            color: #ccc;
        }

        .woocommerce form.login label,.woocommerce form.register label {
            transform: none;
            -webkit-transform: none;
            transition: none;
            -webkit-transition: none;
            margin-bottom: 10px;
        }

        .woocommerce form.login input.input-text,.woocommerce form.register input.input-text {
            border: 1px solid #ddd;
            padding: 5px 0;
            text-indent: 0.5em;
        }
        .woocommerce form.login input.input-text:focus,.woocommerce form.register input.input-text:focus{
            border-bottom-color:#ddd;
        }
        .woocommerce form.login .lost_password{
            margin:0;
            text-align: left;

        }
        .woocommerce form.login .lost_password a,.woocommerce form.login button.button,.woocommerce form.register input.button{text-transform: inherit;}
        .woocommerce form.login .lost_password a{
            color:#219BD7;
            border:none;
            font-weight: 0;
        }
        .woocommerce form.login .lost_password a:hover{
            color:#219BD7;
        }
        .woocommerce form.login button.button, .woocommerce form.register input.button{
            height:45px;
            line-height:45px;
            color:#fff;
            background: #2373f7;
        }
        .woocommerce form.login button.button:hover, .woocommerce form.register input.button:hover{
            background: #2373f7;
        }
    </style>
<?php wc_print_notices(); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<h2 class="text-center login-tabs-nav tabs-nav">
	<span class="line-hover tab-nav active" data-tab="login"><?php esc_html_e( 'Log In', 'sober' ); ?></span>
	<span class="line-hover tab-nav" data-tab="register"><?php esc_html_e( 'Sign Up', 'sober' ); ?></span>
</h2>

<div class="u-columns col2-set tab-panels" id="customer_login">

	<div class="u-column1 col-1 tab-panel active" data-tab="login">
<?php else : ?>

	<h2 class="text-center"><span class="line-hover active"><?php esc_html_e( 'Login', 'sober' ); ?></span></h2>

<?php endif; ?>

		<form class="woocommerce-form woocommerce-form-login login" method="post">

            <?php do_action( 'woocommerce_before_customer_login_form' ); ?>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username', 'sober' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" required />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'sober' ); ?></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" required />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                    <p class="woocommerce-LostPassword lost_password">
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'sober' ); ?></a>
                    </p>
				</label>
			</p>
			<p class="form-row">
				<input type="hidden" name="login" value="<?php esc_attr_e( 'Log In', 'sober' ); ?>" />
				<button type="submit" class="woocommerce-Button button">
					<span class="button-text"><?php esc_html_e( 'Login', 'sober' ); ?></span>
					<span class="loading-icon">
						<span class="bubble"><span class="dot"></span></span>
						<span class="bubble"><span class="dot"></span></span>
						<span class="bubble"><span class="dot"></span></span>
					</span>
				</button>
			</p>


			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="u-column2 col-2 tab-panel" data-tab="register">

		<form method="post" class="register">

			<?php do_action( 'woocommerce_register_form_start' ); ?>

            <?php do_action( 'woocommerce_before_customer_login_form' ); ?>


            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'sober' ); ?></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" required/>
				</p>

			<?php endif; ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'sober' ); ?></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( $_POST['email'] ) : ''; ?>" required />
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'sober' ); ?></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" required />
				</p>

			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-FormRow form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<input type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Sign Up', 'sober' ); ?>" />
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>

<?php endif; ?>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>