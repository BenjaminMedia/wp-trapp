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
        add_action('save_post', [__CLASS__, 'editSavePost'], 999, 2); // Save late
        add_action('before_delete_post', [__CLASS__, 'deletePost']);
        add_action('load-post.php', [__CLASS__, 'loadPost']);

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
        add_action('save_post', [__CLASS__, 'removeSavePost']);
        add_action('do_meta_boxes', [__CLASS__, 'polylangMetaBox'], 10, 2);
        add_action('bp_trapp_after_save_post', [__CLASS__, 'polylangCreateLanguages'], 10, 2);
        add_action('bp_after_delete_trapp', [__CLASS__, 'polylangDeleteTrapp']);
        add_filter('bp_trapp_save_language_post_args', [__CLASS__, 'saveLanguagePostArgs'], 10, 2);
        add_action('admin_init', [__CLASS__, 'pll_post_columns']);
        add_action('pll_get_new_post_translation_link', '__return_false');
    }

    /**
     * Hook listener for save_post.
     *
     * @param int $postId Post id of the edited post.
     *
     * @return void.
     */
    public static function editSavePost($postId, $post)
    {
        // To avoid infinite loops
        remove_action('save_post', [__CLASS__, 'editSavePost'], 999, 2); // Save late

        $events = new Post\Events($postId, $post);
        $events->editSavePost();
    }

    public static function loadPost() {
        add_action('admin_notices', [__CLASS__, 'translationNotices']);
        add_filter('wp_insert_post_data', [__CLASS__, 'insertPostData']);
        add_filter('update_post_metadata', [__CLASS__, 'updatePostMetadata'], 10, 3);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueLockFields']);
        add_filter('in_admin_header', [__CLASS__, 'setLockedFields']);
        add_filter('bp_trapp_locked_field', [__CLASS__, 'filterLockedFields'], 10, 2);
    }

    /**
     * Adds a notification for translations.
     *
     * @return void.
     */
    public static function translationNotices()
    {
        $notice = new Post\TranslationNotices();
        $notice->registerNotice();
    }

    public static function insertPostData($data) {
        $fieldLocker = new Post\FieldLocker();

        return $fieldLocker->filterInsertPostData($data);
    }

    public static function updatePostMetadata($check, $objectId, $metaKey) {
        $fieldLocker = new Post\FieldLocker();

        return $fieldLocker->filterUpdatePostMetadata($check, $objectId, $metaKey);
    }

    public static function enqueueLockFields() {
        $fieldLocker = new Post\FieldLocker();
        $fieldLocker->enqueueLockFields();
    }

    public static function setLockedFields() {
        $fieldLocker = new Post\FieldLocker();
        $fieldLocker->setLockedFields();
    }

    public static function filterLockedFields($return, $field) {
        $fieldLocker = new Post\FieldLocker();

        return $fieldLocker->filterLockedFields($return, $field);
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
     * Hook listener for bp_after_delete_trapp.
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

    public static function pll_post_columns() {
        $pll_columns = new Polylang\Columns();
        $pll_columns->registerColumns();
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
    public static function removeSavePost($postId)
    {
        if (get_post_status($postId) == 'auto-draft') {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || $screen->base != 'post' ) {
            return;
        }

        if (empty($_POST['post_lang_choice'])) {
            // We will handle the translations instead of Polylang
            remove_action('save_post', array(Pll()->filters_post, 'save_post'), 21, 3);
        }
    }
}
