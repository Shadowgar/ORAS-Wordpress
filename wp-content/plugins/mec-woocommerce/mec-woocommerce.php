<?php
/**
 *	Plugin Name: WooCommerce Integration for MEC
 *	Plugin URI: http://webnus.net/modern-events-calendar/
 *	Description: You can purchase ticket and WooCommerce products at the same time.
 *	Author: Webnus
 *	Version: 2.2.0
 *	Text Domain: mec-woocommerce
 *	Domain Path: /languages
 *	Author URI: http://webnus.net
 */


namespace MEC_Woocommerce;

// Don't load directly
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}
/**
 *   Base.
 *
 *   @author     Webnus <info@webnus.biz>
 *   @package    Modern Events Calendar
 *   @since     1.0.0
 */
class Base
{

	/**
	 *  Instance of this class.
	 *
	 *  @since   1.0.0
	 *  @access  public
	 *  @var     MEC_Woocommerce
	 */
	public static $instance;

	public static $is_mec_active;

	/**
	 *  Provides access to a single instance of a module using the Singleton pattern.
	 *
	 *  @since   1.0.0
	 *  @return  object
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		if (defined('MECWOOINTVERSION')) {
			return;
		}

		$this->settingUp();
		$this->preLoad();
		$this->setHooks($this);

		if( is_admin() && static::checkPlugins() ){

			Admin::getInstance();
		}

		do_action('MEC_Woocommerce_init');
	}

	/**
	 *  Global Variables.
	 *
	 *  @since   1.0.0
	 */
	public function settingUp()
	{
		define('MECWOOINTVERSION', '2.2.0');
		define('MECWOOINTDIR', plugin_dir_path(__FILE__));
		define('MECWOOINTURL', plugin_dir_url(__FILE__));
		define('MECWOOINTASSETS', MECWOOINTURL . 'assets/');
		define('MECWOOINTNAME' , 'Woocommerce Integration');
		define('MECWOOINTSLUG' , 'mec-woocommerce');
		define('MECWOOINTOPTIONS' , 'mec_woo_options');
		define('MECWOOINTTEXTDOMAIN' , 'mec-woocommerce');
		define('MECWOOINTMAINFILEPATH' ,__FILE__);
		define('MECWOOINTPABSPATH', dirname(__FILE__));

		register_deactivation_hook( __FILE__, [ $this, 'uninstall' ] );

		$this->add_option();

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * Install (Activation Hook)
	 *
	 * @return void
	 */
	public function install() {
		$allProducts = get_posts(
			array(
				'post_type'   => 'mec-product',
				'numberposts' => -1,
				'post_status' => 'mec_tickets',
			)
		);

		foreach ( $allProducts as $product ) {
			if ( $product->post_status == 'mec_tickets' ) {
				wp_update_post(
					[
						'ID'        => $product->ID,
						'post_type' => 'product',
					]
				);
			}
		}
	}

	/**
	 * Uninstall (Deactivation Hook)
	 *
	 * @return void
	 */
	public static function uninstall() {
		$allProducts = get_posts(
			array(
				'post_type'   => 'product',
				'numberposts' => -1,
				'post_status' => 'mec_tickets',
			)
		);
		foreach ( $allProducts as $product ) {
			if ( $product->post_status == 'mec_tickets' ) {
				wp_update_post(
					[
						'ID'        => $product->ID,
						'post_type' => 'mec-product',
					]
				);
			}
		}
	}

	/**
	 *  Set Hooks
	 *
	 *  @since     1.0.0
	 */
	public function setHooks()
	{
		add_action( 'admin_notices', array( $this, 'upgrade_notice') );
		add_action('wp_ajax_mec-upgrade-woocommerce-db', array( __CLASS__, 'upgrade_db_by_ajax' ) );

		add_action( 'wp_loaded', [ $this, 'load_languages' ] );
		add_action( 'wp_loaded', [ $this, 'install' ] );

		add_filter( 'mec_filter_event_bookings', array( __CLASS__, 'filter_get_event_bookings' ), 10, 3 );

		add_filter('wpml_language_filter_extra_conditions_snippet', array( __CLASS__, 'filter_extra_conditions_snippet' ) );
	}

	/**
	 * Load Translation Languages
	 *
	 * @return void
	 */
	public function load_languages() {

		load_plugin_textdomain(
			'mec-woocommerce',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Add "mec_woo_options" Option
	 *
	 * @return void
	 */
	public function add_option() {
		$addon_information = array(
			'product_name'  => '',
			'purchase_code' => '',
		);
		$has_option        = get_option( 'mec_woo_options', 'false' );
		if ( $has_option == 'false' ) {
			add_option( 'mec_woo_options', $addon_information );
		}
	}

	/**
	 * Plugin Requirements Check
	 *
	 * @since 1.0.0
	 */
	public static function checkPlugins()
	{
		$MEC_Woocommerce = self::instance();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if (is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php') && !class_exists('\MEC')) {
			self::$is_mec_active = false;
			add_action('admin_notices', [$MEC_Woocommerce, 'send_mec_lite_notice']);
			return false;
		} else if ( ! is_plugin_active( 'modern-events-calendar/mec.php' ) && !class_exists('\MEC') ) {
			self::$is_mec_active = false;
			add_action( 'admin_notices', [ $MEC_Woocommerce, 'send_mec_notice' ] );

			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				add_action( 'admin_notices', [ $MEC_Woocommerce, 'send_woo_notice' ] );
				self::$is_mec_active = false;
				return false;
			}

			return false;

		} else {
			if(!defined('MEC_VERSION')) {
				$plugin_data = get_plugin_data( realpath( WP_PLUGIN_DIR . '/modern-events-calendar/mec.php' ) );
				$version     = str_replace( '.', '', $plugin_data['Version'] );
			} else {
				$version = str_replace('.', '', MEC_VERSION);
			}

			if ( $version <= 422 ) {
				self::$is_mec_active = false;
				add_action( 'admin_notices', [ $MEC_Woocommerce, 'send_mec_version_notice' ], 'version' );

				if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					add_action( 'admin_notices', [ $MEC_Woocommerce, 'send_woo_notice' ] );
					self::$is_mec_active = false;
					return false;
				}
				return false;
			}
		}

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', [ $MEC_Woocommerce, 'send_woo_notice' ] );
			self::$is_mec_active = false;
			return false;
		}
		return true;
	}

	/**
	* Is MEC installed ?
	*
	* @since     1.0.0
	*/
	public function is_mec_installed() {
		if(class_exists('\MEC')) {
			return true;
		}
		$file_path         = 'modern-events-calendar/mec.php';
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $file_path ] );
	}

	/**
	* Is WooCommerce installed ?
	*
	* @since     1.0.0
	*/
	public function is_woocommerce_installed() {
		$file_path         = 'woocommerce/woocommerce.php';
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $file_path ] );
	}

	/**
	* Send Admin Notice (MEC)
	*
	* @since 1.0.0
	*/
	public function send_mec_notice( $type = false ) {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}
		if(class_exists('\MEC')) {
			return;
		}

		$plugin = 'modern-events-calendar/mec.php';
		if ( $this->is_mec_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$message        = '<p>' . __( 'WooCommerce Integration is not working because you need to activate the Modern Events Calendar plugin.', 'mec-woocommerce' ) . '</p>';
			$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Modern Events Calendar Now', 'mec-woocommerce' ) ) . '</p>';
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}
			$install_url = 'https://webnus.net/modern-events-calendar/';
			$message     = '<p>' . __( 'WooCommerce Integration is not working because you need to install the Modern Events Calendar plugin', 'mec-woocommerce' ) . '</p>';
			$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Modern Events Calendar Now', 'mec-woocommerce' ) ) . '</p>';
		}
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}

	/**
	* Send Admin Notice (MEC Pro)
	*
	* @since 1.0.0
	*/
	public function send_mec_pro_notice( $type = false ) {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'modern-events-calendar/mec.php';
		if ( $this->is_mec_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$message        = '<p>' . __( 'In order to use the plugin, please Active Modern Events Calendar Pro.', 'mec-woocommerce' ) . '</p>';
			$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Modern Events Calendar Now', 'mec-woocommerce' ) ) . '</p>';
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}
			$install_url = 'https://webnus.net/pricing/#plugins';
			$message     = '<p>' . __( 'In order to use the plugin, please purchase Modern Events Calendar Pro.', 'mec-woocommerce' ) . '</p>';
			$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Purchase Modern Events Calendar Now', 'mec-woocommerce' ) ) . '</p>';
		}
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}

	/**
	* Send Admin Notice (MEC Version)
	*
	* @since 1.0.0
	*/
	public function send_mec_version_notice( $type = false ) {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'modern-events-calendar/mec.php';

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=modern-events-calendar' ), 'install-plugin_' . $plugin );
		$message     = '<p>' . __( 'WooCommerce Integration is not working because you need to install latest version of Modern Events Calendar plugin', 'mec-woocommerce' ) . '</p>';
		$message    .= esc_html__( 'Minimum version required' ) . ': <b> 4.2.3 </b>';
		$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Update Modern Events Calendar Now', 'mec-woocommerce' ) ) . '</p>';

		?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}

	/**
	* Send Admin Notice ( Woocommerce )
	*
	* @since 1.0.0
	*/
	public function send_woo_notice() {

		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}
		$plugin = 'woocommerce/woocommerce.php';
		if ( $this->is_woocommerce_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$message        = '<p>' . __( 'WooCommerce Integration is not working because you need to activate the WooCommerce plugin.', 'mec-woocommerce' ) . '</p>';
			$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate WooCommerce Now', 'mec-woocommerce' ) ) . '</p>';
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}
			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=WooCommerce' ), 'install-plugin_WooCommerce' );
			$message     = '<p>' . __( 'WooCommerce Integration is not working because you need to install the WooCommerce plugin', 'mec-woocommerce' ) . '</p>';
			$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install WooCommerce Now', 'mec-woocommerce' ) ) . '</p>';
		}
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}

	/**
	* Send Admin Notice ( Woocommerce )
	*
	* @since 1.0.0
	*/
	public static function send_mec_lite_notice() {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'modern-events-calendar/mec.php';

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = 'https://webnus.net/modern-events-calendar/';
		$message     = '<p>' . __( 'WooCommerce Integration is not working because you need to install latest version of Modern Events Calendar plugin (PRO)', 'mec-woocommerce' ) . '</p>';
		$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Upgrade to Modern Events Calendar Pro', 'mec-woocommerce' ) ) . '</p>';

		?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php
	}

	/**
	 *  PreLoad
	 *
	 *  @since     1.0.0
	 */
	public function preLoad()
	{
		if(static::checkPlugins()) {
			include_once MECWOOINTDIR . DS . 'core' . DS . 'autoloader.php';
		}
	}

	public static function filter_extra_conditions_snippet($sql) {

		$sql = " AND post_status <> 'mec_tickets' ".$sql;

		return $sql;
	}

	public static function filter_get_event_bookings ( $attendees, $booking_id, $p_occurrence ){

		$p_occurrence = explode(':', $p_occurrence);
		$start_timestamp = $p_occurrence[0];

		foreach($attendees as $k => $attendee){

			if( isset( $attendee['date'] ) && $attendee['date']  ){

				$ex = explode( ':', $attendee['date'] );
				$attendee_start_timestamp = $ex[0];
				if( $attendee_start_timestamp && $attendee_start_timestamp !== $start_timestamp ){
					unset( $attendees[$k] );
					continue;
				}
			}
		}

		return $attendees;
	}


	public function upgrade_notice($type = false) {

		$db_version = get_option( 'mec_woocommerce_db_order_type_version', '1.0.0' );
		if( version_compare( $db_version, MECWOOINTVERSION, '<' ) ) {

			if (!current_user_can('activate_plugins')) {
				return;
			}

			$upgrade_url = admin_url( '?mec_woocommerce_upgrade_db=true' );
			$message        = '<p>'
				. __('Your "WooCommerce Integration for MEC" database needs updating. To do that, click the button below and wait until the operation is over. Do not refresh the page until the end.', 'mec-woocommerce')
				. '<br><b>' . __('Note: if you have many orders, the operation might take longer, please be patient.', 'mec-woocommerce') . '</b>'
				. '</p>';
			$message       .= '<p>' . sprintf('<a href="%s" class="button-primary mec-woocommerce-upgrade-db">%s</a>', $upgrade_url, __('Upgrade Database Now', 'mec-woocommerce')) . '</p>';

			?>
			<script>
				jQuery(document).ready(function($){
					$('.mec-woocommerce-upgrade-db').on('click',function(e){
						e.preventDefault();

						var $btn = $(this);
						$btn.html("<?php echo __( 'Updating Database...', 'mec-woocommerce' ); ?>");
						$.post(
							"<?php echo admin_url('admin-ajax.php'); ?>",
							{
								action: 'mec-upgrade-woocommerce-db',
								nonce: "<?php echo wp_create_nonce( 'mec-upgrade-woocommerce-db' ) ?>",
							},
							function(r){

								if( false == r.done ) {

									$('.mec-woocommerce-upgrade-db').trigger('click');
								}else{

									$btn.html("<?php echo __( 'Database has been upgraded.', 'mec-woocommerce' ); ?>");
								}
							}
						)
					});
				});
			</script>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Upgrade db by ajax
	 *
	 * @return void
	 */
	public static function upgrade_db_by_ajax() {

		if( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'mec-upgrade-woocommerce-db' ) ) {

			return;
		}

		$db_version = get_option( 'mec_woocommerce_db_order_type_version', '1.0.0' );
		if( version_compare( $db_version, MECWOOINTVERSION, '<' ) ) {

			static::upgrade_order_type_db();
			wp_send_json(array(
				'done' => false,
			));
		}else{

			wp_send_json(array(
				'done' => true,
			));
		}
	}

	public static function upgrade_order_type_db() {

		$order_ids = get_posts(array(
			'post_type' => 'shop_order',
			'post_status' => 'all',
			'fields' => 'ids',
			'numberposts' => 100,
			'meta_query' => array(
				array(
					'key' => 'mec_order_type_upgraded',
					'compare' => 'NOT EXISTS',
				)
			)
		));

		if( 0 === count( $order_ids ) ) {

			update_option( 'mec_woocommerce_db_order_type_version', MECWOOINTVERSION );
		}

		foreach( $order_ids as $order_id ) {

			$transactions = \MEC_Woocommerce\Core\Helpers\Products::get_order_transaction_ids( $order_id );
			if( !empty( $transactions ) ){

				update_post_meta( $order_id, 'mec_order_type', 'mec_ticket' );
			} else {

				delete_post_meta( $order_id, 'mec_order_type' );
			}

			update_post_meta( $order_id, 'mec_order_type_upgraded', 'yes' );
		}
	}

} //Base

add_action(
	'plugins_loaded',
	function() {
		if( !class_exists('\MEC') ) {
			return;
		}

		\MEC_Woocommerce\Base::instance();
	}
);

add_filter(
    'MEC_register_gateways',
    function ($gateways) {
        $gateways['MEC_gateway_add_to_woocommerce_cart'] = \MEC_Woocommerce\Core\Gateway\Init::instance();
        return $gateways;
    }
);