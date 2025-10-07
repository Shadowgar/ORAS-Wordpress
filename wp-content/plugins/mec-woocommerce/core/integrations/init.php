<?php

namespace MEC_Woocommerce\Core;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
*  Integrations.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Integrations
{

    /**
    *  Instance of this class.
    *
    *  @since   1.0.0
    *  @access  public
    *  @var     MEC_Woocommerce
    */
    public static $instance;

   /**
    *  The directory of this file
    *
    *  @access  public
    *  @var     string
    */
    public static $dir;

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
        if (self::$instance === null) {
            self::$instance = $this;
        }
        self::settingUp();
        self::preLoad();
        self::setHooks();
    }

   /**
    *  Global Variables.
    *
    *  @since   1.0.0
    */
    public static function settingUp()
    {
        self::$dir     = MECWOOINTDIR . 'core' . DIRECTORY_SEPARATOR . 'integrations';
    }

   /**
    *  Hooks
    *
    *  @since     1.0.0
    */
    public static function setHooks()
    {

    }

   /**
    *  preLoad
    *
    *  @since     1.0.0
    */
    public static function preLoad()
    {
        $main			  = \MEC::getInstance('app.libraries.main');
        $settings = $main->get_settings();
        if ( isset($settings['wc_status']) && $settings['wc_status'] != '1' ){
            foreach (glob(self::$dir . DS . 'parts' . DS . '*.php') as $filename) {
                include $filename;
            }
        }

    }

} //Integrations

Integrations::instance();
