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
    public function setRowTranslations() {
        foreach ($this->row->translations as $translation) {
            $id = $translation['id'];
            $locale = $translation['locale'];

            $this->rowTranslations[$locale] = $id;
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

        $translations = $polylang->model->get_translations('post', $this->post->ID );

        foreach ($this->rowTranslations as $locale => $id) {
            // Polylang is using the slug to set post languages
            $language_slug = current(explode('_', $locale));

            if (!array_key_exists($language_slug, $translations)) {
                $lang_post_args = apply_filters('bp_trapp_save_language_post_args', [
                    'post_title' => '',
                    'post_content' => '',
                    'post_type' => $this->post->post_type,
                ], $this->post, $language_slug);

                $lang_post_id = wp_insert_post($lang_post_args);
                pll_set_post_language($lang_post_id, $language_slug);

                $translations[$language_slug] = $lang_post_id;
            }

            // Update the meta key
            update_post_meta($translations[$language_slug], Post\Events::TRAPP_META_KEY, $id);
        }

        pll_save_post_translations($translations);
        // Think we have to remove polylang save actions
    }

}
