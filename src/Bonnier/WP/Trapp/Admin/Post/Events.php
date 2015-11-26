<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\ServiceTranslation;

class Events
{
    const TRAPP_META_KEY = 'bp_trapp_id';

    public $postId = false;
    public $trappId = false;

    public function __construct($postId)
    {
        $this->postId = $postId;
        $this->trappId = $this->getTrappId();
    }

    public function editPost()
    {
        if (!isset($_POST['send_to_trapp'])){
            return;
        }

        // Exclude auto-draft
        if (get_post_status($this->postId) == 'auto-draft') {
            return;
        }

        // Only specific post types
        $post_type = 'review';
        $post_types = [$post_type]; // TODO Filter to include more post_types

        if (!in_array($post_type, $post_types)) {
            return;
        }

        /**
         * Fired once a post with a TRAPP action has been saved.
         *
         * Specific to the saved post type.
         *
         * @param int     $postId  Post ID.
         */
        do_action('bp_save_trapp_' . $post_type, $this->postId);

        /**
         * Fired once a post with a TRAPP action has been saved.
         *
         * @param int     $postId  Post ID.
         */
        do_action('bp_save_trapp', $this->postId);
    }

    public function savePost()
    {
        if ($this->hasTrappId()) {
            $this->updateTrappRevision();
        } else {
            $this->createTrappRevision();
        }
    }

    public function createTrappRevision()
    {
        $service = new ServiceTranslation;
    }

    public function updateTrappRevision()
    {
        $service = new ServiceTranslation;
    }

    public function hasTrappId()
    {
        if ($this->trappId) {
            return true;
        }

        return false;
    }

    public function getTrappId()
    {
        return get_post_meta($this->postId, self::TRAPP_META_KEY, true);
    }

}
