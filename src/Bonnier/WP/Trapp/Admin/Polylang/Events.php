<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\Mappings;
use Bonnier\WP\Trapp\Admin\Post;

class Events
{

    /**
     * Returned row from Trapp.
     *
     * @var object.
     */
    public $row;

    /**
     * WP_Post object of the saved post.
     *
     * @var object.
     */
    public $post;

    /**
     * Array of translations of the returned row.
     *
     * @var array.
     */
    public $rowTranslations = [];

    /**
     * Sets row and post to the object.
     *
     * @return void.
     */
    public function __construct($row, $post)
    {
        $this->row = $row;
        $this->post = $post;

        $this->setRowTranslations();
    }

    /**
     * Sets translations from row.
     *
     * @return void.
     */
    public function setRowTranslations()
    {
        foreach ($this->row->getRelatedTranslations() as $translation) {
            if ($translation->isOriginal()) {
                continue;
            }

            $locale = $translation->getLocale();

            $this->rowTranslations[$locale] = [
                'id' => $translation->getId(),
                'edit_uri' => $translation->getEditUri(),
            ];
        }
    }

    /**
     * Save languages from the returned row.
     *
     * @return void.
     */
    public function saveLanguages()
    {
        $translations = pll_get_post_translations($this->post->ID);

        foreach ($this->rowTranslations as $locale => $translation) {
            // Polylang is using the slug to set post languages
            $languageSlug = current(explode('_', $locale));

            if (!array_key_exists($languageSlug, $translations)) {
                $translations[$languageSlug] = $this->saveLanguagesPost($languageSlug);
            }

            // Update the meta key
            update_post_meta($translations[$languageSlug], Post\Events::TRAPP_META_KEY, $translation['id']);
            update_post_meta($translations[$languageSlug], Post\Events::TRAPP_META_LINK, $translation['edit_uri']);
        }

        pll_save_post_translations($translations);
    }

    public function saveLanguagesPost($languageSlug) {
        $newPostArgs = apply_filters('bp_trapp_save_language_post_args', [
            'post_title' => $this->post->post_title,
            'post_content' => $this->post->post_content,
            'post_type' => $this->post->post_type,
        ], $this->post, $languageSlug);

        $langPostId = wp_insert_post($newPostArgs);
        pll_set_post_language($langPostId, $languageSlug);

        $this->saveImages($langPostId, $languageSlug);
        $this->saveTerms($langPostId, $languageSlug);

        return $langPostId;
    }

    public function saveTerms($translationId, $languageSlug) {
        $hook = sprintf('bp_trapp_save_%s_taxonomies', $this->post->post_type);
        $taxonomies = apply_filters($hook, []);
        $terms = wp_get_object_terms($this->post->ID, $taxonomies);

        foreach ($terms as $term) {
            if ($translation = Pll()->model->term->get_translation($term->term_id, $languageSlug)) {
                wp_set_post_terms($translationId, $translation, $term->taxonomy, true);
            }
        }
    }

    public function saveImages($translationId, $languageSlug) {
        $images = [];

        if (has_post_thumbnail($this->post->ID)) {
            $thumbnailId = get_post_thumbnail_id($this->post->ID);
            $thumbnailPost = get_post($thumbnailId);

            $images['featured_image'] = [
                'id' => $thumbnailId,
                'post' => $thumbnailPost,
                'type' => 'meta',
                'key' => '_thumbnail_id',
            ];
        }

        $images = apply_filters('bp_trapp_save_images', $images, $this->post->ID);

        foreach ($images as $image) {
            $this->saveImage($translationId, $languageSlug, $image);
        }
    }

    public function saveImage($translationId, $languageSlug, $image) {
        // Check if the translations already exists
        if ($translation = Pll()->model->post->get_translation($image['id'], $languageSlug)) {
            return update_post_meta($translationId, $image['key'], $translation);
        }

        $translationImagePost = $image['post'];

        // Create a new attachment
        $translationImagePost->ID = null;
        $translationImagePost->post_parent = $translationId;

        $translationImageId = wp_insert_attachment($translationImagePost);

        add_post_meta($translationImageId, '_wp_attachment_metadata', get_post_meta($image['id'], '_wp_attachment_metadata', true));
        add_post_meta($translationImageId, '_wp_attached_file', get_post_meta($image['id'], '_wp_attached_file', true));
        add_post_meta($translationImageId, '_wp_attachment_image_alt', get_post_meta($image['id'], '_wp_attachment_image_alt', true));

        $mediaTranslations = Pll()->model->post->get_translations($image['id']);

        if (!$mediaTranslations && $lang = Pll()->model->post->get_language($image['id'])) {
            $mediaTranslations[$lang->slug] = $image['id'];
        }

        $mediaTranslations[$languageSlug] = $translationImageId;

        pll_save_post_translations($mediaTranslations);
        update_post_meta($translationId, $image['key'], $translationImageId);

        do_action('bp_trapp_after_save_post_image', $translationImageId, $image);
    }
}
