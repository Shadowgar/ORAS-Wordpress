<?php

namespace MEC_Woocommerce\Core;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
*  Loader.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Loader
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
        self::registerAutoloadFiles();
        self::loadInits();
    }

   /**
    *  Global Variables.
    *
    *  @since   1.0.0
    */
    public static function settingUp()
    {
        self::$dir     = MECWOOINTDIR . 'core';
    }

   /**
    *  Hooks
    *
    *  @since     1.0.0
    */
    public static function setHooks()
    {
        add_action('admin_init', function () {
            \MEC_Woocommerce\Autoloader::load('MEC_Woocommerce\Core\checkLicense\WoocommerceAddonUpdateActivation');
        });
    }

   /**
    *  preLoad
    *
    *  @since     1.0.0
    */
    public static function preLoad()
    {
        include_once self::$dir . DS . 'autoloader' . DS . 'autoloader.php';
    }

   /**
    *  Register Autoload Files
    *
    *  @since     1.0.0
    */
    public static function registerAutoloadFiles()
    {
        if (!class_exists('\MEC_Woocommerce\Autoloader')) {
            return;
        }

        \MEC_Woocommerce\Autoloader::addClasses(
            [
                // Integrations
                'MEC_Woocommerce\\Admin' => self::$dir . '/admin.php',

                // Integrations
                'MEC_Woocommerce\\Core\\Integrations' => self::$dir . '/integrations/init.php',

                // Gateway
                'MEC_Woocommerce\\Core\\Gateway\\Init' => self::$dir . '/gateway/init.php',

                // Helpers
                'MEC_Woocommerce\\Core\\Helpers\\Products' => self::$dir . '/helpers/products.php',

                // License
                'MEC_Woocommerce\\Core\\checkLicense\\WoocommerceAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',

            ]
        );
    }

   /**
    *  Load Init
    *
    *  @since     1.0.0
    */
    public static function loadInits()
    {
        add_action(
            'init',
            function() {
                \MEC_Woocommerce\Autoloader::load('MEC_Woocommerce\Core\Helpers\Products');
                \MEC_Woocommerce\Autoloader::load('MEC_Woocommerce\Core\Integrations');
            },
            10
        );

        add_action(
            'after_MEC_gateway',
            function() {
                \MEC_Woocommerce\Autoloader::load('MEC_Woocommerce\Core\Gateway\Init');
            },
            10
        );
    }
} //Loader

Loader::instance();
