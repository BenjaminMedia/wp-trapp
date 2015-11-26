<?php
namespace Bonnier\WP\Trapp\Core;

use Bonnier\Trapp;

/**
 * ServiceContent WP class.
 *
 * Initiate Trapp\ServiceTranslation with credentials set from filters.
*/
class ServiceTranslation extends Trapp\ServiceTranslation
{
    /**
     * Initiate IndexSearch\ServiceContent with credentials set from .env
     * and set development to true if APP_ENV is set to local.
     */
    public function __construct()
    {
        $username = $this->getUsername();
        $secret = $this->getSecret();

        if (empty($username) || empty($secret)) {
            return;
        }

        parent::__construct(WA_INDEXSEARCH_USERNAME, WA_INDEXSEARCH_SECRET);

        if ($this->isDevelopment()) {
            $this->setDevelopment(true);
        }
    }

    public function isDevelopment() {
        return apply_filters('bp_trapp_service_development', false );
    }

    protected function getUsername() {
        return apply_filters('bp_trapp_service_username', '');
    }

    protected function getSecret() {
        return apply_filters('bp_trapp_service_secret', '');
    }
}
