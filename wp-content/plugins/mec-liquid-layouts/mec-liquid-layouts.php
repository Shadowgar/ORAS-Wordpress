<?php
/*
    Plugin Name: MEC Liquid Layouts
    Plugin URI: http://webnus.net/modern-events-calendar/
    Description: With this addon you will have some new designs after activate it.
    Author: Webnus
    Version: 1.3.0
    Text Domain: mec-liq
    Domain Path: /languages
    Author URI: http://webnus.net
 */

namespace MEC_Liquid;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * Base.
 *
 * @author     Webnus
 * @package    MEC_Liquid
 * @since      1.0.0
 */
class Base
{
    /**
     * Instance of this class.
     *
     * @since   1.0.0
     * @access  public
     * @var     MEC_Liquid
     */
    public static $instance;

    /**
     * Provides access to a single instance of a module using the singleton pattern.
     *
     * @return  object
     * @since   1.0.0
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
        if (defined('MECLIQUIDVERSION')) {
            return;
        }
        self::settingUp();
        self::preLoad();
        self::setHooks($this);

        do_action('MEC_Liquid_init');
    }

    /**
     * Global Variables.
     *
     * @since   1.0.0
     */
    public static function settingUp()
    {
        define('MECLIQUIDVERSION', '1.3.0');
        define('MECLIQUIDDIR', plugin_dir_path(__FILE__));
        define('MECLIQUIDURL', plugin_dir_url(__FILE__));
        define('MECLIQUIDDASSETS', MECLIQUIDURL . 'assets/');
        define('MECLIQUIDNAME', '	Liquid-view Layouts');
        define('MECLIQUIDSLUG', 'mec-liquid-layouts');
        define('MECLIQUIDOPTIONS', 'mec_liquid_options');
        define('MECLIQUIDTEXTDOMAIN', 'mec-liquid');
        define('MECLIQUIDMAINFILEPATH', __FILE__);
        define('MECLIQUIDABSPATH', dirname(__FILE__));

        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Set Hooks
     *
     * @since     1.0.0
     */
    public static function setHooks($This)
    {
        add_action('wp_enqueue_scripts', [$This, 'frontendScripts'], 0);
        add_action('wp_footer', [$This, 'frontendScripts'], 0);
        add_action('admin_enqueue_scripts', [$This, 'backendScripts']);
        add_action('init', [$This, 'loadLanguages']);
        add_action('wp_head', [$This, 'liquid_ajaxurl']);
    }

    /**
     * Load MEC Liquid Layouts language file from plugin language directory or WordPress language directory
     *
     * @since   1.0.0
     */
    public function loadLanguages()
    {
        load_plugin_textdomain('mec-liq', false, 'mec-liquid-layouts/languages');
    }

    /**
     * Plugin Requirements Check
     *
     * @since 1.0.0
     */
    public static function checkPlugins()
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!self::$instance) {
            self::$instance = static::instance();
        }

        if (!is_plugin_active('modern-events-calendar/mec.php') && !is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php')) {
            return false;
        } elseif (is_plugin_active('modern-events-calendar/mec.php')) {
            $plugin_data = get_plugin_data(realpath(WP_PLUGIN_DIR . '/modern-events-calendar/mec.php'));
            $version     = str_replace('.', '', $plugin_data['Version']);
            if ($version < 540) {
                add_action('admin_notices', [self::$instance, 'MECVersionAdminNotice'], 'version');
                return false;
            }
        } elseif (is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php')) {
            $plugin_data = get_plugin_data(realpath(WP_PLUGIN_DIR . '/modern-events-calendar-lite/modern-events-calendar-lite.php'));
            $version     = str_replace('.', '', $plugin_data['Version']);
            if ($version < 540) {
                add_action('admin_notices', [self::$instance, 'MECLiteVersionAdminNotice'], 'version');
                return false;
            }
        }

        return true;
    }

    /**
     * preLoad
     *
     * @since     1.0.0
     */
    public static function preLoad()
    {
        if (static::checkPlugins()) {
            include_once MECLIQUIDDIR . DS . 'core' . DS . 'autoloader.php';
        }
    }

    public function can_enqueue_scripts() {

        $assets_in_footer_status = \MEC\Base::is_include_assets_in_footer();
        if(
            ( !$assets_in_footer_status && 'wp_footer' === current_action() )
            ||
            ( $assets_in_footer_status && 'wp_enqueue_scripts' === current_action() )
            ){

            return false;
        }

        return \MEC\Base::should_include_assets();
    }

    public function frontendScripts()
    {

        if( $this->can_enqueue_scripts() ){
            $main = \MEC::getInstance('app.libraries.main');
            $settings = $main->get_settings();
            // wp_enqueue_script('mec-date-format-script');
            wp_enqueue_script('mec-nice-scroll');
            wp_enqueue_script('mec-niceselect-script');
            wp_enqueue_style('mec-niceselect-style');

            wp_enqueue_script('mec-liquid-layouts', MECLIQUIDDASSETS . 'mec-liquid-layouts.js', ['jquery', 'mec-frontend-script'], MECLIQUIDVERSION, true);
            wp_enqueue_style('mec-liquid-layouts', MECLIQUIDDASSETS . 'mec-liquid-layouts.css', ['mec-frontend-style'], MECLIQUIDVERSION, 'all');

            if( is_single() && 'mec-events' === get_post_type() && isset($settings['single_single_style']) && $settings['single_single_style'] === 'liquid' ){
                wp_enqueue_style('mec-liquid-layouts-single', MECLIQUIDDASSETS . 'single.css', [], MECLIQUIDVERSION, 'all');
            }
        }
    }

    public function backendScripts(){
        wp_enqueue_script('mec-liquid-backend', MECLIQUIDDASSETS . 'backend.js', ['jquery'], MECLIQUIDVERSION, true);
    }

    function liquid_ajaxurl() {
        echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
    }

} // Base

Base::instance();
