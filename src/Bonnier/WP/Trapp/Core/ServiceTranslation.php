<?php
namespace Bonnier\WP\Trapp\Core;

use Bonnier\Trapp;

/**
 * ServiceContent WP class.
 *
 * Initiates Trapp\ServiceTranslation with credentials set from filters.
*/
class ServiceTranslation extends Trapp\ServiceTranslation
{
    /**
     * Sets credentials from WP filters.
     */
    public function __construct()
    {
        $username = $this->getUsername();
        $secret = $this->getSecret();

        if (empty($username) || empty($secret)) {
            return;
        }

        parent::__construct($username, $secret);

        if ($this->isDevelopment()) {
            $this->setDevelopment(true);
        }
    }

    /**
     * Set development on the ServiceTranslation?
     *
     * @return boolean.
     */
    public function isDevelopment()
    {
        return apply_filters('bp_trapp_service_development', false);
    }

    /**
     * Gets the username for the ServiceTranslation.
     *
     * @return string The ServiceTranslation username.
     */
    protected function getUsername()
    {
        return apply_filters('bp_trapp_service_username', '');
    }

    /**
     * Gets the secret for the ServiceTranslation.
     *
     * @return string The ServiceTranslation secret.
     */
    protected function getSecret()
    {
        return apply_filters('bp_trapp_service_secret', '');
    }
}
