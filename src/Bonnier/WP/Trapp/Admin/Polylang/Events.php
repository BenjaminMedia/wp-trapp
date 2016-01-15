<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp\Plugin;
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
        foreach ($this->row->related_translations as $translation) {
            if ($translation['is_original']) {
                continue;
            }

            $locale = $translation['locale'];

            $this->rowTranslations[$locale] = [
                'id' => $translation['id'],
                'edit_uri' => $translation['edit_uri']
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
        global $polylang;

        $translations = $polylang->model->get_translations('post', $this->post->ID);

        foreach ($this->rowTranslations as $locale => $translation) {
            // Polylang is using the slug to set post languages

            $language_slug = current(explode('_', $locale));

            if (!array_key_exists($language_slug, $translations)) {
                $lang_post_args = apply_filters('bp_trapp_save_language_post_args', [
                    'post_title' => $this->post->post_title,
                    'post_content' => $this->post->post_content,
                    'post_type' => $this->post->post_type,
                ], $this->post, $language_slug);

                $lang_post_id = wp_insert_post($lang_post_args);
                pll_set_post_language($lang_post_id, $language_slug);

                $translations[$language_slug] = $lang_post_id;
            }

            // Update the meta key
            update_post_meta($translations[$language_slug], Post\Events::TRAPP_META_KEY, $translation['id']);
            update_post_meta($translations[$language_slug], Post\Events::TRAPP_META_LINK, $translation['edit_uri']);

            if (!has_post_thumbnail($this->post->ID)) {
                continue;
            }

            $thumbnailId = get_post_thumbnail_id($this->post->ID);
            $thumbnailPost = get_post($thumbnailId);

            // Check if the translations already exists
            if ($translation = $polylang->model->get_translation('post', $thumbnailId, $language_slug)) {
                update_post_meta($translations[$language_slug], '_thumbnail_id', $translation);
            }

            $translationThumbnailPost = $thumbnailPost;

            // Create a new attachment
            $translationThumbnailPost->ID = null;
            $translationThumbnailPost->post_parent = $translations[$language_slug];

            $translationThumbnailId = wp_insert_attachment($translationThumbnailPost);

            add_post_meta($translationThumbnailId, '_wp_attachment_metadata', get_post_meta($thumbnailId, '_wp_attachment_metadata', true));
            add_post_meta($translationThumbnailId, '_wp_attached_file', get_post_meta($thumbnailId, '_wp_attached_file', true));
            add_post_meta($translationThumbnailId, '_wp_attachment_image_alt', get_post_meta($thumbnailId, '_wp_attachment_image_alt', true));

            $mediaTranslations = $polylang->model->get_translations('post', $thumbnailId);

            if (!$mediaTranslations && $lang = $polylang->model->get_post_language($thumbnailId)) {
                $mediaTranslations[$lang->slug] = $thumbnailId;
            }

            $mediaTranslations[$language_slug] = $translationThumbnailId;

            pll_save_post_translations($mediaTranslations);
            update_post_meta($translations[$language_slug], '_thumbnail_id', $translationThumbnailId);

            do_action('bp_trapp_after_save_post_thumbnail', $translationThumbnailId, $thumbnailId);
        }

        pll_save_post_translations($translations);
    }
}
