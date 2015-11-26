<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\ServiceTranslation;

class Events
{
    /**
     * The Trapp id meta key.
     */
    const TRAPP_META_KEY = 'bp_trapp_id';

    /**
     * ID of the saved post.
     *
     * @var integer.
     */
    public $postId = 0;

    /**
     * Trapp ID of the saved post.
     *
     * @var integer.
     */
    public $trappId = 0;

    /**
     * Sets post and Trapp Id to the object.
     *
     * @return void.
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
        $this->trappId = $this->getTrappId();
    }

    /**
     * Validates the send_to_trapp request.
     *
     * @return void.
     */
    public function editPost()
    {
        if (!isset($_POST['send_to_trapp'])) {
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

    /**
     * Create or update a new Trapp revision.
     *
     * @return void.
     */
    public function savePost()
    {
        if ($this->hasTrappId()) {
            $this->updateTrappRevision();
        } else {
            $this->createTrappRevision();
        }
    }

    /**
     * Creates a new Trapp revision.
     *
     * @return void.
     */
    public function createTrappRevision()
    {
        $service = new ServiceTranslation;
    }

    /**
     * Updates an exiting Trapp entry with a new Trapp revision.
     *
     * @return void.
     */
    public function updateTrappRevision()
    {
        $service = new ServiceTranslation;
    }

    /**
     * Validates if a trappId is found.
     *
     * @return boolean.
     */
    public function hasTrappId()
    {
        if ($this->trappId) {
            return true;
        }

        return false;
    }

    /**
     * Fetches the Trapp id meta from the post.
     *
     * @return string.
     */
    public function getTrappId()
    {
        return get_post_meta($this->postId, self::TRAPP_META_KEY, true);
    }
}
