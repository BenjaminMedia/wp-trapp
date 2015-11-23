<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp;
use Bonnier\WP\Trapp\Plugin;
use PLL_Walker_Dropdown;

class MetaBox
{
    /**
     * Registers the Polylang language meta box.
     *
     * @param  string $post_type Post type of the post.
     * @param  string $context   Meta box context.
     *
     * @return void
     */
    public static function registerMetaBox($post_type, $context)
    {
        if ($post_type != 'review' || $context != 'side') {
            return;
        }

        remove_meta_box('ml_box', $post_type, $context);

        $args = [
            'post_type' => $post_type
        ];
        add_meta_box('ml_box', __('Languages', Plugin::TEXT_DOMAIN), [__CLASS__, 'polylangMetaBoxRender'], $post_type, $context, 'high', $args);
    }

    /**
     * Registers the Polylang language meta box.
     *
     * @param  object $post WP_Post post object.
     * @param  array  $metabox Array of metabox arguments.
     *
     * @return void
     */
    public static function polylangMetaBoxRender($post, $metabox)
    {
        global $polylang;

        $post_id = $post->ID;
        $post_type = $metabox['args']['post_type'];

        if ($lg = $polylang->model->get_post_language($post_id)) {
            $lang = $lg;
        } elseif (!empty($_GET['new_lang'])) {
            $lang = $polylang->model->get_language($_GET['new_lang']);
        } else {
            $lang = $polylang->pref_lang;
        }

        $text_domain = Plugin::TEXT_DOMAIN;
        $languages = $polylang->model->get_languages_list();
        $pll_dropdown = new PLL_Walker_Dropdown();
        $dropdown = $pll_dropdown->walk($languages, array(
            'name'     => 'post_lang_choice',
            'class'    => 'tags-input',
            'selected' => $lang ? $lang->slug : '',
            'flag'     => true
        ));

        foreach ($languages as $key_language => $language) {
            if ($language->term_id == $lang->term_id) {
                unset($languages[ $key_language ]);
                $languages = array_values($languages);
                break;
            }
        }

        wp_nonce_field('pll_language', '_pll_nonce');

        $is_autopost = (get_post_status($post) == 'auto-draft');

        include(Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/language.php');

        if (!$is_autopost) {
            include(Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/translations.php');
            include(Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/trapp.php');
        }
    }
}
