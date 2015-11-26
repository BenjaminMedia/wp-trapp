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
        add_filter('bp_trapp_service_username', [__CLASS__, 'serviceUser']);
        add_filter('bp_trapp_service_secret', [__CLASS__, 'serviceSecret']);
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
     * @param  string $user The Trapp secret.
     *
     * @return string $user.
     */
    public static function serviceSecret($secret)
    {
        if (defined('WA_TRAPP_SECRET')) {
            return WA_TRAPP_SECRET;
        }

        return $secret;
    }
}
