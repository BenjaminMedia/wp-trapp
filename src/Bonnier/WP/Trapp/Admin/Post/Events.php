<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\ServiceTranslation;
use Bonnier\Trapp\Translation\TranslationRevision;
use Bonnier\Trapp\Translation\TranslationField;
use DateTime;

class Events
{
    /**
     * The Trapp id meta key.
     */
    const TRAPP_META_KEY = 'bp_trapp_id';

    /**
     * The Trapp master meta key.
     */
    const TRAPP_META_MASTER = 'bp_trapp_master';

    /**
     * The Trapp deadline meta key.
     */
    const TRAPP_META_DEADLINE = 'bp_trapp_deadline';

    /**
     * The Trapp link meta key.
     */
    const TRAPP_META_LINK = 'bp_trapp_link';

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
     * WP_Post object of the saved post.
     *
     * @var object.
     */
    public $post;

    /**
     * Sets post and Trapp Id to the object.
     *
     * @return void.
     */
    public function __construct($postId, $post = '')
    {
        $this->postId = $postId;
        $this->post = !empty($post) ? $post : get_post($this->postId);
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
        $post_types = apply_filters('bp_trapp_post_types', [] );

        if (!in_array($post_type, $post_types)) {
            return;
        }

        /**
         * Fired once a post with a TRAPP action has been saved.
         *
         * Specific to the saved post type.
         *
         * @param int    $postId Post ID.
         * @param object $post   WP_Post object of the saved post.
         */
        do_action('bp_save_trapp_' . $post_type, $this->postId, $this->post);

        /**
         * Fired once a post with a TRAPP action has been saved.
         *
         * @param int    $postId Post ID.
         * @param object $post   WP_Post object of the saved post.
         */
        do_action('bp_save_trapp', $this->postId, $this->post);
    }

    /**
     * Deletes translation from Trapp.
     *
     * @return void.
     */
    public function deletePost()
    {
        // Only the primary post
        if (wp_is_post_revision($this->postId)) {
            return;
        }

        if (!$this->hasTrappId()) {
            return;
        }

        $service = new ServiceTranslation;
// Test delete these
// Master 56619b7bc01443c03e8b456b
#56619b7bc01443c03e8b4575
#56619b7bc01443c03e8b4570

        $translation = $service->getById($this->trappId);
        #$translation->delete();

        $row = $translation->getRow();

        /**
         * Fired once a post with a TRAPP id has been deleted.
         *
         * @param int $postId  Post ID.
         * @param object $post WP_Post object of the deleted post.
         * @param array  $row  Returned row from the Trapp request.
         */
        do_action('bp_delete_trapp', $this->postId, $this->post, $row);die("Noooooooes");
    }

    /**
     * Deletes translations from Trapp master.
     *
     * @return void.
     */
    public function deleteTrappPosts()
    {
        $is_master = get_post_meta($this->postId, Events::TRAPP_META_MASTER, true);

        if (!$is_master) {
            return;
        }

        global $polylang;

        $translations = $polylang->model->get_translations('post', $this->postId);

        if (empty($translations)) {
            return;
        }

        $service = new ServiceTranslation;

        foreach ($translations as $slug => $translation) {
            $trapp_meta = get_post_meta($translation, Events::TRAPP_META_KEY, true);

            if (!$trapp_meta) {
                continue;
            }

            $translation = $service->getById($trapp_meta);
            #$translation->delete();
        }
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
        global $polylang;

        if (empty($_POST['trapp_tr_lang'])) {
            return;
        }

        if (empty($_POST['trapp_deadline'])) {
            return;
        }

        $translation = new ServiceTranslation;

        $deadline = esc_attr($_POST['trapp_deadline']);
        add_post_meta($this->postId, self::TRAPP_META_DEADLINE, $deadline);

        $deadline = new DateTime($deadline);

        $translation->setDeadline($deadline);
        $translation->setLocale($this->getPostLocale());
        $translation->setTitle($this->post->post_title);

        // Create new revision
        $revision = new TranslationRevision();

        if (isset($_POST['trapp_start'])) {
            $translation->setState('state-missing');
        }

        if (!empty($_POST['trapp_comment'])) {
            $translation->setComment(esc_attr($_POST['trapp_comment']));
        }

        $post_group = apply_filters('bp_trapp_post_group', 'Post', $this->postId, $this->post);

        $title = new TranslationField('Title', $this->post->post_title);
        $title->setGroup($post_group);
        $revision->addField($title);

        $post_name = new TranslationField('Name/Slug', $this->post->post_name);
        $post_name->setGroup($post_group);
        $revision->addField($post_name);

        $content = new TranslationField('Body', $this->post->post_content);
        $content->setGroup($post_group);
        $revision->addField($content);

        // Add revision to translation
        $translation->addRevision($revision);

        foreach ($_POST['trapp_tr_lang'] as $trapp_lang => $active) {
            $trapp_lang = esc_attr($trapp_lang);
            $trapp_lang = $polylang->model->get_language($trapp_lang);

            if (!$trapp_lang) {
                continue;
            }

            $locale = $this->filterLocale($trapp_lang->locale);
            $translation->addLanguage($locale);
        }

        $translation->save();

        // Get row data after data
        $row = $translation->getRow();

        // Save Trapp id
        add_post_meta($this->postId, self::TRAPP_META_KEY, $row->id);

        // This is the first saved post and therefore master
        add_post_meta($this->postId, self::TRAPP_META_MASTER, 1);

        do_action('bp_trapp_after_save_post', $row, $this->post);
    }

    /**
     * Updates an exiting Trapp entry with a new revision.
     *
     * @return void.
     */
    public function updateTrappRevision()
    {
        // TODO Reminds alot of the insert flow so maybe merge into a common method
        global $polylang;

        if (empty($_POST['trapp_tr_lang'])) {
            return;
        }

        $service = new ServiceTranslation;
        $translation = $service->getById($this->trappId);
        $translation->setTitle($this->post->post_title);

        if (!empty($_POST['trapp_deadline'])) {
            $deadline = esc_attr($_POST['trapp_deadline']);
            update_post_meta($this->postId, self::TRAPP_META_DEADLINE, $deadline);

            $deadline = new DateTime($deadline);
            $translation->setDeadline($deadline);
        }

        // Create new revision
        $revision = new TranslationRevision();

        if (isset($_POST['trapp_start'])) {
            $translation->setState('state-missing');
        }

        if (!empty($_POST['trapp_comment'])) {
            $translation->setComment(esc_attr($_POST['trapp_comment']));
        }

        $post_group = apply_filters('bp_trapp_post_group', 'Post', $this->postId, $this->post);

        $title = new TranslationField('Title', $this->post->post_title);
        $title->setGroup($post_group);
        $revision->addField($title);

        $post_name = new TranslationField('Name/Slug', $this->post->post_name);
        $post_name->setGroup($post_group);
        $revision->addField($post_name);

        $content = new TranslationField('Body', $this->post->post_content);
        $content->setGroup($post_group);
        $revision->addField($content);

        // Add revision to translation
        $translation->addRevision($revision);

        $post_translations = wp_list_pluck($translation->translations, 'locale');

        foreach ($_POST['trapp_tr_lang'] as $trapp_lang => $active) {
            $trapp_lang = esc_attr($trapp_lang);
            $trapp_lang = $polylang->model->get_language($trapp_lang);

            if (!$trapp_lang) {
                continue;
            }

            $locale = $this->filterLocale($trapp_lang->locale);

            if (in_array($locale, $post_translations)) {
                continue;
            }

            $translation->addLanguage($locale);
        }

        $translation->update();

        // Get row data after data
        $row = $translation->getRow();

        do_action('bp_trapp_after_save_post', $row, $this->post);

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

    public function getPostLocale()
    {
        $locale = pll_get_post_language($this->postId, 'locale');
        $locale = $this->filterLocale($locale);

        return $locale;
    }

    public function filterLocale($locale)
    {
        if ($locale == 'fi') {
            $locale = 'fi_fi';
        }

        return strtolower($locale);
    }
}
