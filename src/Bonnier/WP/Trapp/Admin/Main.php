<?php

namespace Bonnier\WP\Trapp\Admin;

class Main
{
    /**
     * Registers init hooks.
     *
     * @return void
     */
    public function bootstrap()
    {
        add_action('pll_init', [__CLASS__, 'polylangInit']);
        add_action('bp_pll_init', [__CLASS__, 'bpPllInit']);
    }

    /**
     * Registers bp_pll_init action whenever Polylang has been loaded.
     *
     * @return void
     */
    public static function polylangInit()
    {
        do_action('bp_pll_init');
    }

    /**
     * Registers init hooks whenever Polylang has been loaded.
     *
     * @return void
     */
    public static function bpPllInit()
    {
        add_action('do_meta_boxes', [__CLASS__, 'polylangMetaBox'], 10, 2);
    }

    /**
     * Registers the Polylang language meta box.
     *
     * @param  string $post_type Post type of the post.
     * @param  string $context   Meta box context.
     *
     * @return void
     */
    public static function polylangMetaBox($post_type, $context)
    {
        $pll_meta_box = new Polylang\MetaBox();
        $pll_meta_box->registerMetaBox($post_type, $context);
    }
}
