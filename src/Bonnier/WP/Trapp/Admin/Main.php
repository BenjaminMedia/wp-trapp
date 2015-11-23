<?php

namespace Bonnier\WP\Trapp\Admin;

class Main
{
    public function bootstrap()
    {
        add_action('pll_init', [__CLASS__, 'polylangInit']);
        add_action('bp_pll_init', [__CLASS__, 'bpPllInit']);
    }

    public static function polylangInit()
    {
        do_action('bp_pll_init');
    }

    public static function bpPllInit()
    {
        add_action('do_meta_boxes', [__CLASS__, 'polylangMetaBox'], 10, 2);
    }

    public static function polylangMetaBox($post_type, $context)
    {
        $pll_meta_box = new Polylang\MetaBox();
        $pll_meta_box->registerMetaBox($post_type, $context);
    }
}
