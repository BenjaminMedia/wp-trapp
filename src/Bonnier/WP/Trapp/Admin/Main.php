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
        add_action('before_delete_post', [__CLASS__, 'deletePost']);

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
        add_action('edit_post', [__CLASS__, 'removeSavePost']);
        add_action('do_meta_boxes', [__CLASS__, 'polylangMetaBox'], 10, 2);
        add_action('bp_trapp_after_save_post', [__CLASS__, 'polylangCreateLanguages'], 10, 2);
        add_action('bp_before_delete_trapp', [__CLASS__, 'polylangDeleteTrapp'], 10, 2);
        add_filter('bp_trapp_save_language_post_args', [__CLASS__, 'saveLanguagePostArgs'], 10, 2);
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
     * Hook listener for before_delete_post.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void.
     */
    public static function deletePost($postId)
    {
        $events = new Post\Events($postId);
        $events->deletePost();
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
     * Hook listener for bp_delete_trapp.
     *
     * @param int    $postId Post id of the edited post.
     * @param object $post   WP_Post object of the edited post.
     *
     * @return void.
     */
    public static function polylangDeleteTrapp($postId)
    {
        $events = new Post\Events($postId);
        $events->deleteTrappPosts();
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

    /**
     * Filter the new language post args.
     *
     * @param array  $args Arguments to be passed to wp_insert_post.
     * @param object $post WP_Post object of the translated from post.
     *
     * @return array $args.
     */
    public static function saveLanguagePostArgs($args, $post)
    {
        if (get_post_status($post) == 'future') {
            $args['post_status'] = $post->post_status;
            $args['post_date'] = $post->post_date;
            $args['post_date_gmt'] = $post->post_date_gmt;
        }

        return $args;
    }

    /**
     * Remove Polylang save_post hook if the post does not already have a language.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void.
     */
    public static function removeSavePost($postId) {
        if (get_post_status($postId) == 'auto-draft') {
            return;
        }

        global $polylang;

        if (!$polylang->model->get_post_language($postId)) {
            return;
        }

        // We will handle the translations instead of Polylang
        remove_action('save_post', array($polylang->filters_post, 'save_post'), 21, 3);
    }
}
