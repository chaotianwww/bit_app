<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Admin {
	const settings_name_common = 'woocommerce-order-export-common';
	var $activation_notice_option = 'woocommerce-order-export-activation-notice-shown';
	var $step = 30;
	public static $formats = array( 'XLS', 'CSV', 'XML', 'JSON', 'TSV' );
	public static $export_types = array( 'EMAIL', 'FTP', 'HTTP', 'FOLDER', 'SFTP' );
	public $url_plugin;
	public $path_plugin;
	var $methods_allowed_for_guests;

	public function __construct() {
		$this->url_plugin         = dirname( plugin_dir_url( __FILE__ ) ) . '/';
		$this->path_plugin        = dirname( plugin_dir_path( __FILE__ ) ) . '/';
		$this->path_views_default = dirname( plugin_dir_path( __FILE__ ) ) . "/view/";

		if ( is_admin() ) { // admin actions
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			// load scripts on our pages only
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-order-export' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'thematic_enqueue_scripts' ) );
				add_filter( 'script_loader_src', array( $this, 'script_loader_src' ), 10, 2 );
			}
			add_action( 'wp_ajax_order_exporter', array( $this, 'ajax_gate' ) );

			//Add custom bulk export action in Woocomerce orders Table
			add_action('admin_footer-edit.php',  array( $this,'export_orders_bulk_action'));
			add_action('load-edit.php', array( $this,'export_orders_bulk_action_process'));
			add_action('admin_notices', array( $this,'export_orders_bulk_action_notices'));
			//do once
			if( !get_option( $this->activation_notice_option ) )
				add_action('admin_notices', array( $this,'display_plugin_activated_message'));
			
			//extra links in >Plugins
			add_filter( 'plugin_action_links_' . WOE_PLUGIN_BASENAME, array($this,'add_action_links') );
	
			// Add 'Export Status' orders page column header
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_status_column_header' ), 20 );

			// Add 'Export Status' orders page column content
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_status_column_content' ) );

			if ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'shop_order' ) {
				add_action( 'admin_print_styles', array( $this, 'add_order_status_column_style' ) );
			}
		}

		//Pro active ?
		if( self::is_full_version() ) {
			add_filter( 'cron_schedules', array( 'WC_Order_Export_Cron', 'create_custom_schedules' ), 10, 1 );
			add_action( 'wc_export_cron_global', array( 'WC_Order_Export_Cron', 'wc_export_cron_global_f' ) );

			//for direct calls
			add_action( 'wp_ajax_order_exporter_run', array( $this, 'ajax_gate_guest' ) );
			add_action( 'wp_ajax_nopriv_order_exporter_run', array( $this, 'ajax_gate_guest' ) );
			$this->methods_allowed_for_guests = array('run_cron_jobs','run_one_job','run_one_scheduled_job');

			// order actions
			add_action( 'woocommerce_order_status_changed', array( $this, 'wc_order_status_changed' ), 10, 3);
			// activate CRON hook if it was removed
			add_action( 'wp_loaded', function() {
				$all_jobs = WC_Order_Export_Manage::get_export_settings_collection( WC_Order_Export_Manage::EXPORT_SCHEDULE );
				if ( $all_jobs )
					WC_Order_Export_Cron::install_job();
			} );
		}

		$this->settings = self::load_main_settings();
	}

	public function add_order_status_column_header( $columns ) {
		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {
			if ( 'order_actions' === $column_name ) {
				$label = __( 'Export Status', 'woocommerce-order-export' );
				$new_columns['woe_export_status'] = $label;
			}
			$new_columns[ $column_name ] = $column_info;
		}
		return $new_columns;
	}

	public function add_order_status_column_content( $column ) {
		global $post;

		if ( 'woe_export_status' === $column ) {
			$is_exported = false;

			if ( get_post_meta( $post->ID, 'woe_order_exported', true ) ) {
				$is_exported = true;
			}

			if( $is_exported ) {
				echo '<span class="dashicons dashicons-yes" style="color: #2ea2cc"></span>';
			} else {
				echo '<span class="dashicons dashicons-minus"></span>';
			}
		}
	}

	function add_order_status_column_style() {
		$css = '.widefat .column-woe_export_status { width: 45px; text-align: center; }';
		wp_add_inline_style( 'woocommerce_admin_styles', $css );
	}

	public function install() {
		//wp_clear_scheduled_hook( "wc_export_cron_global" ); //debug
		if( self::is_full_version() )
			WC_Order_Export_Cron::install_job();
	}

	public function display_plugin_activated_message() {
		?>
		<div class="notice notice-success is-dismissible">
        <p><?php _e( 'Advanced Orders Export For WooCommerce is available <a href="admin.php?page=wc-order-export">on this page</a>.', 'woocommerce-order-export' ); ?></p>
		</div>
		<?php
		update_option( $this->activation_notice_option, true );
	}

	public function add_action_links( $links ) {
		$mylinks  =  array(
			'<a href="admin.php?page=wc-order-export">'. __('Settings', 'woocommerce-order-export'). '</a>',
			'<a href="https://algolplus.com/plugins/documentation-order-export-woocommerce/" target="_blank">'. __('Docs', 'woocommerce-order-export'). '</a>',
			'<a href="https://algolplus.freshdesk.com" target="_blank">'. __('Support', 'woocommerce-order-export'). '</a>'
		);
		return array_merge( $mylinks, $links);
	}

	public function uninstall() {
		wp_clear_scheduled_hook( "wc_export_cron_global" );
		delete_option( $this->activation_notice_option );

		if( self::is_full_version() ) {
			WC_Order_Export_EDD::getInstance()->edd_woe_force_deactivate_license();
		}
	}

	static function load_main_settings() {
		return array_merge(
			array(
			'cron_tasks_active' => '1',
			'ajax_orders_per_step' => '30',
			'limit_button_test' => '1',
			'cron_key' => '1234',
			),
			get_option( self::settings_name_common, array() )
		);
	}
	static function save_main_settings() {
		// update main settings here!
		$settings = filter_input_array(INPUT_POST, array(
			'cron_tasks_active' => FILTER_VALIDATE_BOOLEAN,
			'ajax_orders_per_step' => FILTER_VALIDATE_INT,
			'limit_button_test' => FILTER_SANITIZE_STRING,
			'cron_key' => FILTER_SANITIZE_STRING
		) );
		update_option( self::settings_name_common, $settings );
	}


	function load_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-order-export' );
		load_textdomain( 'woocommerce-order-export', WP_LANG_DIR . '/woocommerce-order-export/woocommerce-order-export-' . $locale . '.mo' );

		load_plugin_textdomain( 'woocommerce-order-export', false,
			plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/i18n/languages' );
	}

	public function add_menu() {
		if( apply_filters('woe_current_user_can_export', true) ) {
			if ( current_user_can( 'manage_woocommerce' ) )
				add_submenu_page( 'woocommerce', __( 'Export Orders', 'woocommerce-order-export' ),__( 'Export Orders', 'woocommerce-order-export' ), 'view_woocommerce_reports', 'wc-order-export', array( $this, 'render_menu' ) );
			else // add after Sales Report!
				add_menu_page( __( 'Export Orders', 'woocommerce-order-export' ),__( 'Export Orders', 'woocommerce-order-export' ), 'view_woocommerce_reports', 'wc-order-export', array( $this, 'render_menu' ) , null, '55.7');
		}
	}

	public function render_menu() {
		$this->render( 'main', array( 'WC_Order_Export' => $this, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		$active_tab = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'export';
		if ( method_exists( $this, 'render_tab_' . $active_tab ) ) {
			$this->{'render_tab_' . $active_tab}();
		}
	}

	public function render_tab_export() {
		$this->render( 'tab/export', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'WC_Order_Export' => $this ) );
	}

    public function render_tab_tools() {
		$this->render( 'tab/tools', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'WC_Order_Export' => $this ) );
	}

    public function render_tab_settings() {
		$this->render( 'tab/settings', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'settings' => $this->settings ) );
	}

	public function render_tab_license() {
		$this->render( 'tab/license', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'WC_Order_Export' => $this ) );
	}

    public function render_tab_help() {
		$this->render( 'tab/help', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'WC_Order_Export' => $this ) );
	}

	public function render_tab_order_actions() {
		$wc_oe     = isset( $_REQUEST['wc_oe'] ) ? $_REQUEST['wc_oe'] : '';
		$ajaxurl   = admin_url( 'admin-ajax.php' );
		$mode = WC_Order_Export_Manage::EXPORT_ORDER_ACTION;
		$all_items = WC_Order_Export_Manage::get_export_settings_collection( $mode );
		$show      = array(
			'date_filter'      => false,
			'export_button'    => false,
			'preview_actions'  => false,
			'destinations'     => true,
			'schedule'         => false,
			'sort_orders'      => false,
			'order_filters'    => true,
			'product_filters'  => true,
			'customer_filters' => true,
			'billing_filters'  => true,
			'shipping_filters' => true,
		);
		switch ( $wc_oe ) {
			case 'add_action':
				end( $all_items );
				$next_id = key( $all_items ) + 1;
				$this->render( 'settings-form', array(
					'mode'            => $mode,
					'id'              => $next_id,
					'WC_Order_Export' => $this,
					'ajaxurl'         => $ajaxurl,
					'show'            => $show
				) );
				return;
			case 'edit_action':
				$item_id = isset( $_REQUEST[ 'action_id' ] ) ? $_REQUEST[ 'action_id' ] : '';
				$clone = isset( $_REQUEST[ 'clone' ] ) ? $_REQUEST[ 'clone' ] : '';
				if ( $clone ) {
					$item_id = WC_Order_Export_Manage::clone_export_settings( $mode, $item_id );
				}
				if ($item_id) {
					$this->render( 'settings-form', array(
						'mode'            => $mode,
						'id'              => $item_id,
						'WC_Order_Export' => $this,
						'ajaxurl'         => $ajaxurl,
						'show'            => $show
					) );
				}
				return;
			case 'delete':
				$item_id = isset( $_REQUEST['action_id'] ) ? $_REQUEST['action_id'] : '';
				if ( $item_id ) {
					unset( $all_items[ $item_id ] );
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_items );
				}
				break;
			case 'change_status':
				$item_id = isset( $_REQUEST['action_id'] ) ? $_REQUEST['action_id'] : '';
				if ( $item_id ) {
					$all_items[ $item_id ]['active'] = $_REQUEST['status'];
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_items );
				}
				$url = remove_query_arg( array( 'wc_oe', 'action_id', 'status' ) );
				wp_redirect( $url );
				break;
		}
		$this->render( 'tab/order-actions', array( 'ajaxurl' => $ajaxurl, 'WC_Order_Export' => $this, 'tab' => 'order_actions' ) );
	}

	public function render_tab_schedules() {
		$wc_oe    = isset( $_REQUEST['wc_oe'] ) ? $_REQUEST['wc_oe'] : '';
		$ajaxurl  = admin_url( 'admin-ajax.php' );
		$mode = WC_Order_Export_Manage::EXPORT_SCHEDULE;
		$all_jobs = WC_Order_Export_Manage::get_export_settings_collection( $mode );
		$show = array(
			'date_filter'   => true,
			'export_button' => true,
			'destinations'  => true,
			'schedule'      => true,
		);
		switch ( $wc_oe ) {
			case 'add_schedule':
				end( $all_jobs );
				$next_id = key( $all_jobs ) + 1;
				$this->render( 'settings-form', array(
					'mode'            => $mode,
					'id'              => $next_id,
					'WC_Order_Export' => $this,
					'ajaxurl'         => $ajaxurl,
					'show'            => $show
				) );
				return;
			case 'edit_schedule':
				$schedule_id = isset( $_REQUEST[ 'schedule_id' ] ) ? $_REQUEST[ 'schedule_id' ] : '';
				$clone = isset( $_REQUEST[ 'clone' ] ) ? $_REQUEST[ 'clone' ] : '';
				if ( $clone ) {
					$schedule_id = WC_Order_Export_Manage::clone_export_settings( $mode, $schedule_id );
				}
				if ( $schedule_id ) {
					$this->render( 'settings-form', array(
						'mode'            => $mode,
						'id'              => $schedule_id,
						'WC_Order_Export' => $this,
						'ajaxurl'         => $ajaxurl,
						'show'            => $show
					) );
				}
				return;
			case 'delete_schedule':
				$schedule_id = isset( $_REQUEST['schedule_id'] ) ? $_REQUEST['schedule_id'] : '';
				if ( $schedule_id ) {
					unset( $all_jobs[ $schedule_id ] );
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_jobs );
				}
				break;
			case 'change_status_schedule':
				$schedule_id = isset( $_REQUEST['schedule_id'] ) ? $_REQUEST['schedule_id'] : '';
				if ( $schedule_id ) {
					$all_jobs[ $schedule_id ]['active'] = $_REQUEST['status'];
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_jobs );
				}
				$url = remove_query_arg( array( 'wc_oe', 'schedule_id', 'status' ) );
				wp_redirect( $url );
				break;
		}
		$this->render( 'tab/schedules', array( 'ajaxurl' => $ajaxurl, 'WC_Order_Export' => $this ) );
	}

	public function render_tab_profiles() {
		$wc_oe    = isset( $_REQUEST['wc_oe'] ) ? $_REQUEST['wc_oe'] : '';
		$ajaxurl  = admin_url( 'admin-ajax.php' );
		$mode = WC_Order_Export_Manage::EXPORT_PROFILE;
		$all_items = WC_Order_Export_Manage::get_export_settings_collection( $mode );
		$show = array(
			'date_filter'   => true,
			'export_button' => true,
			'destinations'  => true,
			'schedule'      => false,
		);
		switch ( $wc_oe ) {
			case 'add_profile':
				end( $all_items );
				$next_id = key( $all_items ) + 1;
				$this->render( 'settings-form', array(
					'mode'            => $mode,
					'id'              => $next_id,
					'WC_Order_Export' => $this,
					'ajaxurl'         => $ajaxurl,
					'show'            => $show
				) );
				return;
			case 'edit_profile':
				$profile_id = isset( $_REQUEST['profile_id'] ) ? $_REQUEST['profile_id'] : '';
				$clone = isset( $_REQUEST[ 'clone' ] ) ? $_REQUEST[ 'clone' ] : '';
				if ( $clone ) {
					$profile_id = WC_Order_Export_Manage::clone_export_settings( $mode, $profile_id );
				}
				if ( $profile_id ) {
					$this->render( 'settings-form', array(
						'mode'            => $mode,
						'id'              => $profile_id,
						'WC_Order_Export' => $this,
						'ajaxurl'         => $ajaxurl,
						'show'            => $show
					) );
				}
				return;
			case 'copy_profile_to_scheduled':
				$profile_id  = isset( $_REQUEST['profile_id'] ) ? $_REQUEST['profile_id'] : '';
				$schedule_id = WC_Order_Export_Manage::advanced_clone_export_settings( $profile_id, $mode, WC_Order_Export_Manage::EXPORT_SCHEDULE );
				$url = remove_query_arg( 'profile_id' );
				$url = add_query_arg( 'tab', 'schedules', $url );
				$url = add_query_arg( 'wc_oe', 'edit_schedule', $url );
				$url = add_query_arg( 'schedule_id', $schedule_id, $url );
				wp_redirect( $url );
				break;
			case 'copy_profile_to_actions':
				$profile_id  = isset( $_REQUEST['profile_id'] ) ? $_REQUEST['profile_id'] : '';
				$schedule_id = WC_Order_Export_Manage::advanced_clone_export_settings( $profile_id, $mode, WC_Order_Export_Manage::EXPORT_ORDER_ACTION );
				$url = remove_query_arg( 'profile_id' );
				$url = add_query_arg( 'tab', 'order_actions', $url );
				$url = add_query_arg( 'wc_oe', 'edit_action', $url );
				$url = add_query_arg( 'action_id', $schedule_id, $url );
				wp_redirect( $url );
				break;
			case 'delete_profile':
				$profile_id = isset( $_REQUEST['profile_id'] ) ? $_REQUEST['profile_id'] : '';
				if ( $profile_id ) {
					unset( $all_items[ $profile_id ] );
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_items );
				}
				break;
			case 'change_profile_bulk_action':
				$profile_id = isset( $_REQUEST['profile_id'] ) ? $_REQUEST['profile_id'] : '';
				if ( $profile_id ) {
					if( $_REQUEST['status'] ) {
						$all_items[ $profile_id ][ 'use_as_bulk' ] = 'on';
					} else {
						unset( $all_items[ $profile_id ][ 'use_as_bulk' ] );
					}
					WC_Order_Export_Manage::save_export_settings_collection( $mode, $all_items );
				}
				$url = remove_query_arg( array( 'wc_oe', 'profile_id', 'status' ) );
				wp_redirect( $url );
				break;
		}

		//code to copy default settings as profile
		$profiles = WC_Order_Export_Manage::get_export_settings_collection( $mode );
		$free_job = WC_Order_Export_Manage::get_export_settings_collection( WC_Order_Export_Manage::EXPORT_NOW);
		if(empty( $profiles )  AND !empty( $free_job ) ) {
			$free_job['title'] = __('Copied from "Export Now"', 'woocommerce-order-export' );
			$free_job['mode'] = $mode;
			$profiles[1] = $free_job;
			update_option( WC_Order_Export_Manage::settings_name_profiles, $profiles);
		}

		$this->render( 'tab/profiles', array( 'ajaxurl' => $ajaxurl, 'WC_Order_Export' => $this ) );
	}


	public function thematic_enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'jquery-style',
			'//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css' );
		$this->enqueue_select2_scripts();
		wp_enqueue_script( 'export', $this->url_plugin . 'assets/js/export.js' );
		wp_enqueue_style( 'export', $this->url_plugin . 'assets/css/export.css' );
	}

	private function enqueue_select2_scripts() {
		wp_enqueue_script( 'select22', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js',
			array( 'jquery' ), '4.0.3' );
		$locale          = get_locale();
		$select2_locales = array(
			'de_DE' => 'de',
			'de_CH' => 'de',
			'ru_RU' => 'ru',
			'pt_BR' => 'pt-BR',
			'pt_PT' => 'pt',
			'zh_CN' => 'zh-CN',
			'fr_FR' => 'fr',
			'es_ES' => 'es',
		);
		if ( array_key_exists( $locale, $select2_locales ) ) {
			$select2_locale = $select2_locales[ $locale ];
			wp_enqueue_script( "select22-{$select2_locale}",
				"https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/i18n/{$select2_locale}.js" );
		}
		wp_enqueue_style( 'select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css',
			array(), WC_VERSION );
	}

	public function script_loader_src($src, $handle) {
		// don't load ANY select2.js / select2.min.js  and OUTDATED select2.full.js
		if (!preg_match('/\/select2\.full\.js\?ver=[1-3]/', $src) && !preg_match('/\/select2\.min\.js/', $src) && !preg_match('/\/select2\.js/', $src) ) {
			return $src;
		}
	}

	public function render( $view, $params = array(), $path_views = null ) {
		extract( $params );
		if ( $path_views ) {
			include $path_views . "$view.php";
		} else {
			include $this->path_views_default . "$view.php";
		}
	}

	public function get_value( $arr, $name ) {
		$arr_name = explode( ']', $name );
		$arr_name = array_map( function ( $name ) {
			if ( substr( $name, 0, 1 ) == '[' ) {
				$name = substr( $name, 1 );
			}
			return trim( $name );
		}, $arr_name );
		$arr_name = array_filter( $arr_name );

		foreach ( $arr_name as $value ) {
			$arr = isset( $arr[ $value ] ) ? $arr[ $value ] : "";
		}
		return $arr;
	}

	//on status change
	public function wc_order_status_changed( $order_id, $old_status, $new_status ) {
		$all_items = get_option( WC_Order_Export_Manage::settings_name_actions, array() );
		if ( empty( $all_items ) ) {
			return;
		}
		$old_status = is_string( $old_status ) && strpos( $old_status, 'wc-' ) !== 0 ? "wc-{$old_status}" : $old_status;
		$new_status = is_string( $new_status ) && strpos( $new_status, 'wc-' ) !== 0 ? "wc-{$new_status}" : $new_status;

		$this->changed_order_id = $order_id;
		add_filter( 'woe_sql_get_order_ids_where', array($this, "filter_by_changed_order"), 10, 2 );

		$logger = function_exists( "wc_get_logger" ) ? wc_get_logger() : false; //new logger in 3.0+
		$logger_context = array( 'source' => 'woocommerce-order-export' );

		foreach ( $all_items as $key=>$item ) {
			$item = WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_ORDER_ACTION, $key );
			if ( isset( $item['active'] ) && ! $item['active'] ) {
				continue;
			}
			// use empty for ANY status
			if ( ( empty( $item['from_status'] ) OR  in_array( $old_status, $item['from_status'] ) )
			     AND
			     ( empty( $item['to_status'] ) OR in_array( $new_status, $item['to_status'] ) )
				) {
				do_action('woe_order_action_started', $order_id, $item );
				$result = WC_Order_Export_Engine::build_files_and_export( $item );
				$output = sprintf( __('Status change rule #%s for order #%s. Result: %s', 'woocommerce-order-export' ), $key, $order_id, $result);
				// log if required
				if( $logger AND !empty($item['log_results']) )
					$logger->info( $output, $logger_context );

				do_action('woe_order_action_completed', $order_id,  $item , $result );
			}
		}
		remove_filter( 'woe_sql_get_order_ids_where', array($this, "filter_by_changed_order"), 10 );
	}

	public function filter_by_changed_order ( $where, $settings ) {
		$where[] = "orders.ID = " . $this->changed_order_id;
		return $where;
	}

	// AJAX part
	// calls ajax_action_XXXX
	public function ajax_gate() {
		if ( isset( $_REQUEST['method'] ) ) {
			$method = $_REQUEST['method'];
			if ( method_exists( 'WC_Order_Export_Ajax', $method ) ) {
				$_POST = array_map('stripslashes_deep', $_POST);
				$ajax = new WC_Order_Export_Ajax();
				$ajax->$method();
			}
		}
		die();
	}

	//TODO: debug!
	public function ajax_gate_guest() {
		if ( isset( $_REQUEST['method'] )  AND in_array($_REQUEST['method'],$this->methods_allowed_for_guests) ) {
			$method = $_REQUEST['method'];
			if ( method_exists( 'WC_Order_Export_Ajax', $method ) ) {
				$_POST = array_map('stripslashes_deep', $_POST);
				$ajax = new WC_Order_Export_Ajax();
				$ajax->$method();
			}
		}
		die();
	}

	function export_orders_bulk_action() {
		global $post_type;

		if ( $post_type == 'shop_order' ) {
			$settings = WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_NOW );
			WC_Order_Export_Manage::set_correct_file_ext( $settings );
			$items   = array();
			if( ! empty($settings['format']) ) {
				$items[] = array(
						'label' => sprintf( __( 'Export as %s', 'woocommerce-order-export' ), $settings['format'] ),
						'value' => 'woe_export_selected_orders'
				);
			}

			$items[] = array(
				'label' => __( 'Mark exported', 'woocommerce-order-export' ),
				'value' => 'woe_mark_exported'
			);
			$items[] = array(
				'label' => __( 'Unmark exported', 'woocommerce-order-export' ),
				'value' => 'woe_unmark_exported'
			);

			$all_items = WC_Order_Export_Manage::get_export_settings_collection( WC_Order_Export_Manage::EXPORT_PROFILE );
			foreach ( $all_items as $job_id => $job ) {
				if ( isset( $job['use_as_bulk'] ) ) {
					$items[] = array(
							'label' => sprintf( __( "Export as profile '%s'", 'woocommerce-order-export' ), $job['title'] ),
							'value' => 'woe_export_selected_orders_profile_' . $job_id
					);
				}
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					<?php foreach($items as $item): ?>
					jQuery('<option>').val('<?php echo $item['value'] ?>').text("<?php echo $item['label'] ?>").appendTo("select[name='action']");
					jQuery('<option>').val('<?php echo $item['value'] ?>').text("<?php echo $item['label'] ?>").appendTo("select[name='action2']");
					<?php endforeach ?>
				});
			</script>
			<?php
		}
	}

	function export_orders_bulk_action_process() {
		global $sendback;

		$wp_list_table	 = _get_list_table( 'WP_Posts_List_Table' );
		$action			 = $wp_list_table->current_action();
		switch ( $action ) {
			case 'woe_export_selected_orders':
				if (!isset($_REQUEST[ 'post' ]))
					return;
				$post_ids	 = $_REQUEST[ 'post' ];
				$sendback	 = $_REQUEST[ '_wp_http_referer' ];
				$sendback = add_query_arg( array( 'export_bulk_profile' => 'now', 'ids' => join( ',', $post_ids ) ), $sendback );
				break;
			case 'woe_mark_exported':
				if (!isset($_REQUEST[ 'post' ]))
					return;
				$post_ids	 = $_REQUEST[ 'post' ];
				foreach( $post_ids as $post_id ) {
					update_post_meta( $post_id, 'woe_order_exported', 1 );
				}
				$sendback = $_REQUEST[ '_wp_http_referer' ];// echo "sandback={$sendback}";die();
				$sendback = add_query_arg( array( 
					'woe_bulk_mark_exported'   => count( $post_ids ),
					'woe_bulk_unmark_exported' => false,
				), $sendback );
				break;
			case 'woe_unmark_exported':
				if (!isset($_REQUEST[ 'post' ]))
					return;
				$post_ids	 = $_REQUEST[ 'post' ];
				foreach( $post_ids as $post_id ) {
					delete_post_meta( $post_id, 'woe_order_exported');
				}
				$sendback = $_REQUEST[ '_wp_http_referer' ];
				$sendback = add_query_arg( array(
					'woe_bulk_mark_exported'   => false,
					'woe_bulk_unmark_exported' => count( $post_ids ) 
				), $sendback );
				break;
			default:
				if ( preg_match( '/woe_export_selected_orders_profile_(\d+)/', $action, $matches ) ) {
					if ( isset( $matches[1] ) ) {
						$id = $matches[1];
					}
					else {
						return;
					}
					if ( ! isset($_REQUEST[ 'post' ] ) )
						return;
					$post_ids	 = $_REQUEST[ 'post' ];
					$sendback	 = $_REQUEST[ '_wp_http_referer' ];
					$sendback = add_query_arg( array( 'export_bulk_profile' => $id, 'ids' => join( ',', $post_ids ) ), $sendback );
					break;
				}
				return;
		}

		wp_redirect( $sendback );
		exit();
	}

	function export_orders_bulk_action_notices() {

		global $post_type, $pagenow;

		if ( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset( $_REQUEST[ 'export_bulk_profile' ] ) ) {
			$url = admin_url( 'admin-ajax.php' ) . "?action=order_exporter&method=export_download_bulk_file&export_bulk_profile=" . $_REQUEST[ 'export_bulk_profile' ] . "&ids=" . $_REQUEST[ 'ids' ];
			wp_redirect($url);
			exit();
			/* unused code
			//$message = sprintf( __( 'Orders exported. <a href="%s">Download report.</a>' ,'woocommerce-order-export'), $url );
			$message = __( 'Orders exported.','woocommerce-order-export');

			echo "<div class='updated'><p>{$message}</p></div><iframe width=0 height=0 style='display:none' src='$url'></iframe>";

			// must remove this arg from pagination url
			add_filter('removable_query_args', array($this, 'fix_table_links') );
			*/
		} else if ( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset( $_REQUEST[ 'woe_bulk_mark_exported' ] ) ) {
			$count = intval( $_REQUEST[ 'woe_bulk_mark_exported' ] );
			printf(
				'<div id="message" class="updated fade">' .
				_n( '%s order marked.', '%s orders marked.', $count, 'woocommerce-order-export' )
				. '</div>',
				$count
			);

		} else if ( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset( $_REQUEST[ 'woe_bulk_unmark_exported' ] ) ) {
			$count = intval( $_REQUEST[ 'woe_bulk_unmark_exported' ] );
			printf(
				'<div id="message" class="updated fade">' .
				_n( '%s order unmarked.', '%s orders unmarked.', $count, 'woocommerce-order-export' )
				. '</div>',
				$count
			);
		}
	}

	function fix_table_links( $args ) {
		$args[] = 'export_bulk_profile';
		$args[] = 'ids';
		return $args;
	}

	function must_run_ajax_methods() {
		// wait admin ajax!
		if ( basename($_SERVER['SCRIPT_NAME']) != "admin-ajax.php" )
				return false;
		// our method MUST BE called
		return isset($_REQUEST['action'])  AND ($_REQUEST['action'] == "order_exporter"  OR $_REQUEST['action'] == "order_exporter_run" );
	}

	public static function is_full_version() {
		return defined( 'WOE_VERSION' );
    }
    
	public static function user_can_add_custom_php() {
		return apply_filters('woe_user_can_add_custom_php', current_user_can('edit_themes') );
    }
    
}
