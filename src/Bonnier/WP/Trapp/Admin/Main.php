<?php

namespace Bonnier\WP\Trapp\Admin;

class Main
{
    public function bootstrap()
    {
        add_action('pll_init', [__CLASS__, 'pll_init']);
        add_action('bp_pll_init', [__CLASS__, 'bp_pll_init']);
    }

    public static function pll_init() {
        do_action('bp_pll_init');
    }

    public static function bp_pll_init() {
        add_action('do_meta_boxes', [__CLASS__, 'polylang_meta_box'], 10, 2);
    }

    public static function polylang_meta_box($post_type, $context) {
        $pll_meta_box = new Polylang\MetaBox();
        $pll_meta_box->register_meta_box($post_type, $context);
    }
}
