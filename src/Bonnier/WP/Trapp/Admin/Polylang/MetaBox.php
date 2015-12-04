<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp;
use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Admin\Post\Events;
use PLL_Walker_Dropdown;

class MetaBox
{
    /**
     * Registers the Polylang language meta box.
     *
     * @param  string $post_type Post type of the post.
     * @param  string $context   Meta box context.
     *
     * @return void.
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
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueDatePicker']);
    }

    /**
     * Registers the Polylang language meta box.
     *
     * @param  object $post WP_Post post object.
     * @param  array  $metabox Array of metabox arguments.
     *
     * @return void.
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

        // These shold really exist in some other methods.. whenever the structure has been very defined
        $is_autopost = (get_post_status($post) == 'auto-draft');
        $is_master = get_post_meta($post->ID, Events::TRAPP_META_MASTER, true);
        $has_trapp_key = get_post_meta($post->ID, Events::TRAPP_META_KEY, true);

        if ($is_autopost) {
            include(self::getView('admin/metabox-translations-post/language.php'));
        } else {
            include(self::getView('admin/metabox-translations-post/translations.php'));

            if ($is_master || !$has_trapp_key) {
                $deadline = get_post_meta($post->ID, Events::TRAPP_META_DEADLINE, true);

                if (empty($deadline)) {
                    $deadline = date('Y-m-d', current_time('timestamp'));
                }

                include(self::getView('sadmin/metabox-translations-post/trapp.php'));
            }
        }
    }

    /**
     * Registers datepicker for the deadline field.
     *
     * @return void.
     */
    public static function enqueueDatePicker()
    {
        $script_src = Trapp\instance()->plugin_url . 'js/bp-trapp-datepicker.js';
        $style_src = Trapp\instance()->plugin_url . 'css/bp-trapp-datepicker.css';
        $deps = [
            'jquery',
            'jquery-ui-core',
            'jquery-ui-datepicker'
        ];

        wp_enqueue_script('bp-trapp-datepicker', $script_src, $deps);
        wp_enqueue_style('bp-trapp-datepicker', $style_src);
    }

    /**
     * Returns view by path.
     *
     * @param  string $path Path to the view.
     *
     * @return string       Full include path.
     */
    public static function getView($path = '') {
        $dir = Trapp\instance()->plugin_dir . 'views/';

        return $dir . $path;
    }
}
