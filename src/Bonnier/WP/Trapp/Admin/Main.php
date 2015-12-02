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
        add_action('edit_post', [__CLASS__, 'editPost'], 10, 2);

        // Hook into plugin actions
        add_action('bp_save_trapp', [__CLASS__, 'saveTrapp'], 10, 2);
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
        add_action('bp_trapp_after_save_post', [__CLASS__, 'polylangCreateLanguages'], 10, 2);
    }

    /**
     * Hook listener for edit_post.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void.
     */
    public static function editPost($postId, $post)
    {
        $events = new Post\Events($postId, $post);
        $events->editPost();
    }

    /**
     * Hook listener for bp_save_trapp.
     *
     * @param int    $postId Post id of the edited post.
     * @param object $post   WP_Post object of the edited post.
     *
     * @return void.
     */
    public static function saveTrapp($postId, $post)
    {
        $events = new Post\Events($postId, $post);
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

    /**
     * Hook listener for bp_save_trapp when Polylang is active.
     *
     * @param object $row  Returned row from Trapp.
     * @param object $post WP_Post object of the edited post.
     *
     * @return void.
     */
    public static function polylangCreateLanguages($row, $post)
    {
        $events = new Polylang\Events($row, $post);
        $events->saveLanguages();
    }
}
