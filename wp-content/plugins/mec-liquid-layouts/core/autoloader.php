<?php

namespace MEC_Liquid\Core;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * Loader.
 *
 * @author      Webnus
 * @package     MEC_Liquid
 * @since       1.0.0
 */
class Loader
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
     * The directory of the file.
     *
     * @access  public
     * @var     string
     */
    public static $dir;

    /**
     * Provides access to a single instance of a module using the singleton pattern.
     *
     * @since   1.0.0
     * @return  object
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
        self::settingUp();
        self::preLoad();
        self::setHooks();
        self::registerAutoloadFiles();
        self::loadInits();
    }

    /**
     * Global Variables.
     *
     * @since   1.0.0
     */
    public static function settingUp()
    {
        self::$dir     = MECLIQUIDDIR . 'core';
    }

    /**
     * Hooks
     *
     * @since     1.0.0
     */
    public static function setHooks()
    {
        add_action('admin_init', function () {
            \MEC_Liquid\Autoloader::load('MEC_Liquid\Core\checkLicense\LiquidViewAddonUpdateActivation');
        });
    }

    /**
     * preLoad
     *
     * @since     1.0.0
     */
    public static function preLoad()
    {
        include_once self::$dir . DS . 'autoloader' . DS . 'autoloader.php';
    }

    /**
     * Register Autoload Files
     *
     * @since     1.0.0
     */
    public static function registerAutoloadFiles()
    {
        if (!class_exists('\MEC_Liquid\Autoloader')) {
            return;
        }

        \MEC_Liquid\Autoloader::addClasses(
            [
                'MEC_Liquid\\Core\\pluginBase\\MecLiquid' => self::$dir . '/pluginBase/mec-liquid.php',
                
                // Licensing
                'MEC_Liquid\\Core\\checkLicense\\LiquidViewAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',
            ]
        );
    }

    /**
     * Load Init
     *
     * @since     1.0.0
     */
    public static function loadInits()
    {
        \MEC_Liquid\Autoloader::load('MEC_Liquid\Core\pluginBase\MecLiquid');
    }
} //Loader

Loader::instance();
