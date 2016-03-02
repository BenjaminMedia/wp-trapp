<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp;
use Bonnier\WP\Trapp\Core\Endpoints;
use Bonnier\WP\Trapp\Core\Mappings;

class Columns
{
    public function registerColumns() {
        foreach ( Mappings::postTypes() as $post_type ) {
            add_action('manage_' . $post_type . '_posts_custom_column', [__CLASS__, 'pll_before_post_column'], 8, 2);
            add_action('manage_' . $post_type . '_posts_custom_column', [__CLASS__, 'pll_after_post_column'], 12, 2);
        }

        add_action('load-edit.php', [__CLASS__, 'loadEdit']);
    }

    public static function loadEdit() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueColumnsStyles']);
    }

    public static function pll_before_post_column( $column, $post_id ) {
        if ( false === strpos( $column, 'language_' ) ) {
            return;
        }

        $language = Pll()->model->get_language( substr( $column, 9 ) );
        $translation_id = pll_get_post($post_id, $language->slug);

        if (get_post_meta($translation_id, Endpoints::TRAPP_META_TRANSLATED, true) ) {
            echo '<span class="trapp-state-translated">';
        }
    }

    public static function pll_after_post_column( $column, $post_id ) {
        if ( false === strpos( $column, 'language_' ) ) {
            return;
        }

        $language = Pll()->model->get_language( substr( $column, 9 ) );
        $translation_id = pll_get_post($post_id, $language->slug);

        if (get_post_meta($translation_id, Endpoints::TRAPP_META_TRANSLATED, true) ) {
            echo '</span>';
        }
    }

    /**
     * Registers styles for the columns.
     *
     * @return void.
     */
    public static function enqueueColumnsStyles()
    {
        $src = Trapp\instance()->plugin_url . 'css/bp-trapp-columns.css';

        wp_enqueue_style('bp-trapp-metabox', $src);
    }
}
