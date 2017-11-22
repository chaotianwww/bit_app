<?php
/**
 * The plugin main file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wpgenie.org
 * @since             1.0.0
 * @package           wc_groupbuy
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Group Buy
 * Plugin URI:        http://wpgenie.org/wc-groupbuy/
 * Description:       WooCommerce extension for groupbuy product type. Enables groupbuy on every WooCommerce powered WordPress website.
 * Version:           1.1.7
 * Author:            wpgenie
 * Author URI:        http://wpgenie.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc_groupbuy
 * Domain Path:       /languages
 *
 * WC requires at least: 2.6.0
 * WC tested up to: 3.2.1
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-wc-groupbuy-activator.php
     */
    function activate_wc_groupbuy() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-groupbuy-activator.php';
        wc_groupbuy_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-wc-groupbuy-deactivator.php
     */
    function deactivate_wc_groupbuy() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-groupbuy-deactivator.php';
        wc_groupbuy_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_wc_groupbuy' );
    register_deactivation_hook( __FILE__, 'deactivate_wc_groupbuy' );

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-wc-groupbuy.php';

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(  __FILE__  ) . 'includes/class-wc-groupbuy-i18n.php';


    function run_wc_groupbuy() {

        global $wc_groupbuy;

        $plugin = plugin_basename(__FILE__);

        $wc_groupbuy = new wc_groupbuy($plugin);
        $wc_groupbuy->run();
    }

    add_action( 'woocommerce_init' , 'run_wc_groupbuy');

} else {

    add_action('admin_notices', 'wc_groupbuy_error_notice');

    function wc_groupbuy_error_notice(){
        global $current_screen;
        if($current_screen->parent_base == 'plugins'){
                echo '<div class="error"><p>WooCommerce Group Buy '.__('requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.', 'wc_groupbuy').'</p></div>';
        }
    }

    $plugin = plugin_basename(__FILE__);

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if(is_plugin_active($plugin)){
            deactivate_plugins( $plugin);
    }

    if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
}
