<?php
namespace Bonnier\WP\Trapp\Core;

class Mappings
{

    /**
     * Sets credentials from WP filters.
     */
    public static function postTypes()
    {
        return apply_filters('bp_trapp_post_types', [] );
    }
}
