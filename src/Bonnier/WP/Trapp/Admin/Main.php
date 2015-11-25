<?php

namespace Bonnier\WP\Trapp\Admin;

class Main
{
    /**
     * Registers init hooks.
     *
     * @return void
     */
    public function bootstrap()
    {
        add_action('pll_init', [__CLASS__, 'polylangInit']);
        add_action('bp_pll_init', [__CLASS__, 'bpPllInit']);
        add_action('edit_post', [__CLASS__, 'editPost']);
    }

    /**
     * Registers bp_pll_init action whenever Polylang has been loaded.
     *
     * @return void
     */
    public static function polylangInit()
    {
        do_action('bp_pll_init');
    }

    /**
     * Registers init hooks whenever Polylang has been loaded.
     *
     * @return void
     */
    public static function bpPllInit()
    {
        add_action('do_meta_boxes', [__CLASS__, 'polylangMetaBox'], 10, 2);
    }

    /**
     * Hook listener for edit_post.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void
     */
    public static function editPost($postId)
    {
        if (!isset($_POST['send_to_trapp'])) {
            return;
        }

        // Exclude auto-draft
        if (get_post_status($postId) == 'auto-draft') {
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
        do_action('save_trapp_' . $post_type, $postId);

        /**
         * Fired once a post with a TRAPP action has been saved.
         *
         * @param int     $postId  Post ID.
         */
        do_action('save_trapp', $postId);
    }

    /**
     * Registers the Polylang language meta box.
     *
     * @param  string $post_type Post type of the post.
     * @param  string $context   Meta box context.
     *
     * @return void
     */
    public static function polylangMetaBox($post_type, $context)
    {
        $pll_meta_box = new Polylang\MetaBox();
        $pll_meta_box->registerMetaBox($post_type, $context);
    }
}
