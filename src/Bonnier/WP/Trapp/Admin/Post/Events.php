<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\Endpoints;
use Bonnier\WP\Trapp\Core\Mappings;
use Bonnier\WP\Trapp\Core\ServiceTranslation;
use Bonnier\Trapp\Translation\TranslationRevision;
use Bonnier\Trapp\Translation\TranslationField;
use GuzzleHttp\Exception\RequestException;
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
    public function editSavePost()
    {
        if (!isset($_POST['send_to_trapp'])) {
            return;
        }

        // Exclude auto-draft
        if (get_post_status($this->postId) == 'auto-draft') {
            return;
        }

        // Only specific post types
        $post_type = get_post_type($this->postId);
        $post_types = Mappings::postTypes();

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

        try {
            $service = $service->getById($this->trappId);
            $row = $service->delete();

            /**
             * Fired once a post with a TRAPP id has been deleted.
             *
             * @param int    $postId  Post ID.
             * @param object $post WP_Post object of the deleted post.
             * @param array  $row  Returned row from the Trapp request.
             */
            do_action('bp_after_delete_trapp', $this->postId, $this->post, $row);

        } catch (RequestException $e) {
            /**
             * Fired once a post with a TRAPP id has been deleted and a RequestException has been returned.
             *
             * @param int    $postId  Post ID.
             * @param object $post    WP_Post object of the deleted post.
             * @param object $e       Returned RequestException.
             */
            do_action('bp_after_delete_trapp_exception', $this->postId, $this->post, $e);
        }

    }

    /**
     * Deletes translations Trapp Ids when the post is master.
     *
     * @return void.
     */
    public function deleteTrappPosts()
    {
        $is_master = get_post_meta($this->postId, self::TRAPP_META_MASTER, true);

        if (!$is_master) {
            return;
        }

        $translations = pll_get_post_translations($this->postId);

        if (empty($translations)) {
            return;
        }

        foreach ($translations as $slug => $translation) {
            if ($translation == $this->postId) {
                continue;
            }

            $trapp_meta = get_post_meta($translation, self::TRAPP_META_KEY, true);

            if (!$trapp_meta) {
                continue;
            }

            delete_post_meta($translation, self::TRAPP_META_KEY);
            delete_post_meta($translation, self::TRAPP_META_LINK);
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
        if (empty($_POST['trapp_tr_lang']) && !$this->hasPostTranslations()) {
            return;
        }

        if (empty($_POST['trapp_deadline'])) {
            return;
        }

        $service = new ServiceTranslation;

        $deadline = esc_attr($_POST['trapp_deadline']);

        // Save Deadline
        add_post_meta($this->postId, self::TRAPP_META_DEADLINE, $deadline);

        $deadline = new DateTime($deadline);

        $service->setDeadline($deadline);
        $service->setLocale($this->getPostLocale());
        $service->setTitle($this->post->post_title);
        $service->setUpdateEndpointUri($this->getUpdateEndpoint());

        if (!empty($_POST['trapp_comment'])) {
            $service->setComment(esc_attr($_POST['trapp_comment']));
        }

        if (isset($_POST['trapp_start'])) {
            $service->setState('state-missing');
        }

        $fieldGroups = Mappings::getFields(get_post_type($this->post));

        foreach ($fieldGroups as $fieldGroup) {
            foreach ($fieldGroup['fields'] as $field) {
                $field['group'] = $fieldGroup['title'];

                $field = Mappings::translationField($field, $this->postId, $this->post);

                if ( ! empty( $field ) ) {
                    if (is_array($field)) {
                        foreach ($field as $singleField) {
                            $service->addField($singleField);
                        }
                    } else {
                        $service->addField($field);
                    }
                }
            }
        }

        if (!empty($_POST['trapp_tr_lang'])) {
            $languages = $_POST['trapp_tr_lang'];
        } else {
            $languages = $this->getPostTranslations();
        }

        $languages = array_keys($languages);

        foreach ($languages as $trapp_lang) {
            $trapp_lang = esc_attr($trapp_lang);
            $trapp_lang = PLL()->model->get_language($trapp_lang);

            if (!$trapp_lang) {
                continue;
            }

            $locale = $this->filterLocale($trapp_lang->locale);
            $service->addTranslatation($locale);
        }

        $row = $service->save();

        // Save Trapp ID
        add_post_meta($this->postId, self::TRAPP_META_KEY, $row->getId());

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
        $service = new ServiceTranslation;
        $service = $service->getById($this->trappId);

        if (!empty($_POST['trapp_deadline'])) {
            $deadline = esc_attr($_POST['trapp_deadline']);
            update_post_meta($this->postId, self::TRAPP_META_DEADLINE, $deadline);

            $deadline = new DateTime($deadline);
            $service->setDeadline($deadline);
        }

        if (!empty($_POST['trapp_comment'])) {
            $service->setComment(esc_attr($_POST['trapp_comment']));
        }

        if (isset($_POST['trapp_start'])) {
            $service->setState('state-missing');
        }

        $newFields = [];
        $arrayIgnore = [];
        $serviceFields = $service->getFields();
        $fieldGroups = Mappings::getFields(get_post_type($this->post));

        foreach ($fieldGroups as $fieldGroup) {
            foreach ($fieldGroup['fields'] as $field) {
                $field['group'] = $fieldGroup['title'];

                foreach ($serviceFields as $fieldId => $serviceField) {

                    if ($field['label'] == $serviceField->getLabel()) {
                        $value = Mappings::getValue($field['type'], $this->postId, $this->post, $field['args']);

                        if (!empty($value)) {
                            $serviceFields[$fieldId]->setValue($value);
                        }

                        continue 2;
                    } elseif ($field['type'] == 'post_meta_array') {
                        $value = Mappings::getValue($field['type'], $this->postId, $this->post, $field['args']);
                        $label = $serviceField->getLabel();

                        if (is_array($value) && array_key_exists($label, $value)) {
                            $serviceFields[$fieldId]->setValue($value[$label]);
                            $arrayIgnore[] = $label;
                        }
                    }
                }

                $newFields[] = $field;
            }
        }

        $service->setFields($serviceFields);

        if (!empty($newFields)) {
            foreach ($newFields as $newField) {
                $field = Mappings::translationField($newField, $this->postId, $this->post, $arrayIgnore);

                if (!empty($field)) {
                    if (is_array($field)) {
                        foreach ($field as $singleField) {
                            $service->addField($singleField);
                        }
                    } else {
                        $service->addField($field);
                    }
                }
            }
        }

        $post_translations = [];

        foreach ($service->getRelatedTranslations() as $serviceTranslation) {
            if ($serviceTranslation->isOriginal()) {
                continue;
            }

            $post_translations[] = $serviceTranslation->getLocale();
        }

        if (!empty($_POST['trapp_tr_lang'])) {
            foreach ($_POST['trapp_tr_lang'] as $trapp_lang => $active) {
                $trapp_lang = esc_attr($trapp_lang);
                $trapp_lang = PLL()->model->get_language($trapp_lang);

                if (!$trapp_lang) {
                    continue;
                }

                $locale = $this->filterLocale($trapp_lang->locale);

                if (in_array($locale, $post_translations)) {
                    continue;
                }

                $service->addTranslatation($locale);
            }
        }

        $row = $service->update();

        do_action('bp_trapp_after_save_post', $row, $this->post);
    }

    public function getUpdateEndpoint() {
        $endpoints = new Endpoints;
        $rest_url = get_rest_url();
        $route = $endpoints->getNameSpace() . '/' . $endpoints::ROUTE_UPDATE_CALLBACK;

        return $rest_url . $route;
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

    public function getPostLocale($postId = 0)
    {
        if (empty($postId)) {
            $postId = $this->postId;
        }

        $locale = pll_get_post_language($postId, 'locale');
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

    public function hasPostTranslations()
    {
        return (!empty($this->getPostTranslations()));
    }

    public function getPostTranslations()
    {
        $language = pll_get_post_language($this->postId);
        $translations = pll_get_post_translations($this->postId);

        if (array_key_exists($language, $translations)) {
            unset($translations[$language]);
        }

        return $translations;
    }
}
