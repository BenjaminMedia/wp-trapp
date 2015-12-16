<?php

namespace Bonnier\WP\Trapp\Core;

use Bonnier\WP\Trapp\Admin;
use Bonnier\WP\Trapp\Frontend;

class Bootstrap
{
    /**
     * Run plugin core bootstrap.
     *
     * @return void.
     */
    public function bootstrap()
    {
        if (is_admin()) {
            $admin = new Admin\Main;
            $admin->bootstrap();
        }

        $this->coreBootstrap();
    }

    /**
     * Registers core init hooks.
     *
     * @return void.
     */
    public function coreBootstrap()
    {
        add_action('rest_api_init', [__CLASS__, 'registerEndpoints' ] );
        add_filter('bp_trapp_service_username', [__CLASS__, 'serviceUser']);
        add_filter('bp_trapp_service_secret', [__CLASS__, 'serviceSecret']);
        add_filter('bp_trapp_save_app_code', [__CLASS__, 'saveAppCode']);
        add_filter('bp_trapp_save_brand_code', [__CLASS__, 'saveBrandCode']);
    }

    /**
     * Registers plugin endpoints.
     *
     * @return void.
     */
    public static function registerEndpoints()
    {
        $endPoints = new Endpoints;
        $endPoints->registerRoutes();
    }

    /**
     * Read the Trapp username from the constant 'WA_TRAPP_USERNAME'.
     *
     * @param  string $user The Trapp username.
     *
     * @return string $user.
     */
    public static function serviceUser($user)
    {
        if (defined('WA_TRAPP_USERNAME')) {
            return WA_TRAPP_USERNAME;
        }

        return $user;
    }

    /**
     * Read the Trapp secret from the constant 'WA_TRAPP_SECRET'.
     *
     * @param  string $secret The Trapp secret.
     *
     * @return string $secret.
     */
    public static function serviceSecret($secret)
    {
        if (defined('WA_TRAPP_SECRET')) {
            return WA_TRAPP_SECRET;
        }

        return $secret;
    }

    /**
     * Read the Trapp app code from the constant 'WA_APP_CODE'.
     *
     * @param  string $app_code The Trapp app code.
     *
     * @return string $app_code.
     */
    public static function saveAppCode($app_code)
    {
        if (defined('WA_APP_CODE')) {
            return WA_APP_CODE;
        }

        return $app_code;
    }

    /**
     * Read the Trapp brand code from the constant 'WA_BRAND_CODE'.
     *
     * @param  string $brand_code The Trapp brand code.
     *
     * @return string $brand_code.
     */
    public static function saveBrandCode($brand_code)
    {
        if (defined('WA_BRAND_CODE')) {
            return WA_BRAND_CODE;
        }

        return $brand_code;
    }
}
