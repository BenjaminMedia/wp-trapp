<?php

namespace Bonnier\WP\Trapp\Admin;

use Bonnier\WP\Trapp\Admin\Post;

class Main
{
    /**
     * Registers admin init hooks.
     *
     * @return void.
     */
    public function bootstrap()
    {
        // Register own actions
        add_action('pll_init', [__CLASS__, 'polylangInit']);
        add_action('bp_pll_init', [__CLASS__, 'bpPllInit']);
        add_action('edit_post', [__CLASS__, 'editPost']);

        // Hook into plugin actions
        add_action('bp_save_trapp', [__CLASS__, 'saveTrapp']);
    }

    /**
     * Registers bp_pll_init action whenever Polylang has been loaded.
     *
     * @return void.
     */
    public static function polylangInit()
    {
        do_action('bp_pll_init');
    }

    /**
     * Registers init hooks whenever Polylang has been loaded.
     *
     * @return void.
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
     * @return void.
     */
    public static function editPost($postId)
    {
        $events = new Post\Events($postId);
        $events->editPost();
    }

    /**
     * Hook listener for bp_save_trapp.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void.
     */
    public static function saveTrapp($postId)
    {
        $events = new Post\Events($postId);
        $events->savePost();
    }

    /**
     * Registers the Polylang language meta box.
     *
     * @param  string $post_type Post type of the post.
     * @param  string $context   Meta box context.
     *
     * @return void.
     */
    public static function polylangMetaBox($post_type, $context)
    {
        $pll_meta_box = new Polylang\MetaBox();
        $pll_meta_box->registerMetaBox($post_type, $context);
    }
}
