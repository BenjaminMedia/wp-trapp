<?php

namespace Bonnier\WP\Trapp\Core;

use Bonnier\WP\Trapp\Admin;
use Bonnier\WP\Trapp\Frontend;

class Bootstrap
{
    /**
     * Run plugin core bootstrap.
     *
     * @return void.
     */
    public function bootstrap()
    {
        if (is_admin()) {
            $admin = new Admin\Main;
            $admin->bootstrap();
        }

        $this->coreBootstrap();
    }

    /**
     * Registers core init hooks.
     *
     * @return void.
     */
    public function coreBootstrap()
    {
        add_action('rest_api_init', [__CLASS__, 'registerEndpoints' ] );
        add_action('pll_init', [__CLASS__, 'polylangInit']);
        add_action('bp_trapp_core_pll_init', [__CLASS__, 'bpPllInit']);
        add_filter('bp_trapp_service_username', [__CLASS__, 'serviceUser']);
        add_filter('bp_trapp_service_secret', [__CLASS__, 'serviceSecret']);
        add_filter('bp_trapp_service_development', [__CLASS__, 'serviceDevelopment']);
        add_filter('bp_trapp_save_app_code', [__CLASS__, 'saveAppCode']);
        add_filter('bp_trapp_save_brand_code', [__CLASS__, 'saveBrandCode']);
        add_filter('bp_trapp_get_wp_post_value', [__CLASS__, 'filterGetWpPost'], 10, 4);
        add_filter('bp_trapp_update_wp_post_value', [__CLASS__, 'filterUpdateWpPost'], 10, 4);
        add_filter('bp_trapp_get_post_meta_value', [__CLASS__, 'filterGetPostMeta'], 10, 4);
        add_filter('bp_trapp_update_post_meta_value', [__CLASS__, 'filterUpdatePostMeta'], 10, 4);
        add_filter('bp_trapp_get_post_meta_array_value', [__CLASS__, 'filterGetPostMetaArray'], 10, 4);
        add_filter('bp_trapp_update_post_meta_array_value', [__CLASS__, 'filterUpdatePostMetaArray'], 10, 4);
        add_filter('bp_trapp_get_image_display_value', [__CLASS__, 'filterGetImageDisplay'], 10, 4);
        add_filter('bp_trapp_get_image_wp_post_value', [__CLASS__, 'filterGetImageWpPost'], 10, 4);
        add_filter('bp_trapp_update_image_wp_post_value', [__CLASS__, 'filterUpdateImageWpPost'], 10, 4);
        add_filter('bp_trapp_get_image_post_meta_value', [__CLASS__, 'filterGetImagePostMeta'], 10, 4);
        add_filter('bp_trapp_update_image_post_meta_value', [__CLASS__, 'filterUpdateImagePostMeta'], 10, 4);
    }

    public static function filterGetWpPost ($value, $postId, $post, $args)
    {
        if (!array_key_exists('key', $args)) {
            return $value;
        }

        $key = $args['key'];

        if (!empty($post->$key)) {
            $value = $post->$key;
        }

        return $value;
    }

    public static function filterUpdateWpPost($update, $post, $value, $args)
    {
        if (!array_key_exists('key', $args)) {
            return $update;
        }

        $key = $args['key'];

        $updatePost['ID'] = $post->ID;
        $updatePost[$key] = $value;

        // Update post
        $update = wp_update_post($updatePost, true);

        return $update;
    }

    public static function filterGetPostMeta($value, $postId, $post, $args)
    {
        if (!array_key_exists('key', $args)) {
            return $value;
        }

        $value = get_post_meta($postId, $args['key'], true);

        return $value;
    }

    public static function filterUpdatePostMeta($update, $post, $value, $args)
    {
        if (!array_key_exists('key', $args)) {
            return $update;
        }

        $update = update_post_meta($post->ID, $args['key'], $value);

        return $update;
    }

    public static function filterGetPostMetaArray($value, $postId, $post, $args)
    {
        if (!array_key_exists('key', $args)) {
            return $value;
        }

        $values = get_post_meta($postId, $args['key'], true);

        if (!$values) {
            return $values;
        }

        foreach ($values as $key => $value) {
            if (empty($value)) {
                unset($values[$key]);
            }
        }

        return $values;
    }

    public static function filterUpdatePostMetaArray($update, $post, $value, $args)
    {
        $args = apply_filters('bp_trapp_update_post_meta_array_args', $args, $post );

        if (!array_key_exists('key', $args)) {
            return $update;
        }

        $values = get_post_meta($post->ID, $args['key'], true);

        if (!is_array($values)) {
            $values = [];
        }

        $arrayKey = $args['array_key'];

        $values[$arrayKey] = $value;

        $update = update_post_meta($post->ID, $args['key'], $values);

        return $update;
    }

    public static function filterGetImageDisplay($value, $postId, $post, $args) {
        if (!array_key_exists('image_key', $args) ) {
            return $value;
        }

        $imageId = get_post_meta($postId, $args['image_key'], true);

        if (!$imageId) {
            return $value;
        }

        $image = get_post($imageId);

        if (!$image) {
            return $value;
        }

        return wp_get_attachment_image_url($imageId, 'full');
    }

    public static function filterGetImageWpPost($value, $postId, $post, $args)
    {
        if (!array_key_exists('image_key', $args) || !array_key_exists('key', $args)) {
            return $value;
        }

        $imageId = get_post_meta($postId, $args['image_key'], true);

        if (!$imageId) {
            return $value;
        }

        $image = get_post($imageId);

        if (!$image) {
            return $value;
        }

        $key = $args['key'];

        if (!empty($image->$key)) {
            $value = $image->$key;
        }

        return $value;
    }

    public static function filterUpdateImageWpPost($update, $post, $value, $args)
    {
        if (!array_key_exists('image_key', $args) || !array_key_exists('key', $args)) {
            return $update;
        }

        $imageId = get_post_meta($post->ID, $args['image_key'], true);

        if (!$imageId) {
            return $value;
        }

        $image = get_post($imageId);

        if (!$image) {
            return $value;
        }

        $key = $args['key'];

        $updatePost['ID'] = $image->ID;
        $updatePost[$key] = $value;

        // Update post
        $update = wp_update_post($updatePost, true);

        return $update;
    }

    public static function filterGetImagePostMeta($value, $postId, $post, $args)
    {
        if (!array_key_exists('image_key', $args) || !array_key_exists('key', $args)) {
            return $value;
        }

        $image = get_post_meta($postId, $args['image_key'], true);

        if (!$image) {
            return $value;
        }

        $value = get_post_meta($image, $args['key'], true);

        return $value;
    }

    public static function filterUpdateImagePostMeta($update, $post, $value, $args)
    {
        if (!array_key_exists('image_key', $args) || !array_key_exists('key', $args)) {
            return $update;
        }

        $image = get_post_meta($post->ID, $args['image_key'], true);

        if (!$image) {
            return $update;
        }

        $update = update_post_meta($image, $args['key'], $value);

        return $update;
    }

    /**
     * Registers plugin endpoints.
     *
     * @return void.
     */
    public static function registerEndpoints()
    {
        $endPoints = new Endpoints;
        $endPoints->registerRoutes();
    }

    /**
     * Registers bp_pll_init action whenever Polylang has been loaded.
     *
     * @return void.
     */
    public static function polylangInit()
    {
        do_action('bp_trapp_core_pll_init');
    }

    /**
     * Registers init hooks whenever Polylang has been loaded.
     *
     * @return void.
     */
    public static function bpPllInit() {
        add_action('bp_trapp_update_trapp', [__CLASS__, 'update_trapp_set_status'], 10, 2);
        add_action('save_post', [__CLASS__, 'clear_post_filters_links_cache']);
    }

    /**
     * Sets the updated Trapp post to the same status as the master.
     *
     * @return void.
     */
    public static function update_trapp_set_status($post, $master) {
        $post_status = get_post_status($post);
        $master_status = get_post_status($master);

        if ($post_status == $master_status) {
            return;
        }

        $args = [
            'ID' => $post->ID,
            'post_status' => $master_status,
        ];

        if ($master_status == 'future') {
            $args['post_date'] = $master->post_date;
            $args['post_date_gmt'] = $master->post_date_gmt;
        }

        wp_update_post($args);
    }

    /**
     * Avoid post link cache when a link is updated - See PLL_Frontend_Filters_Links::post_type_link
     */
    public static function clear_post_filters_links_cache($postId) {
        $cacheKey = 'post:' . $postId;
        Pll()->filters_links->cache->clean($cacheKey);
    }

    /**
     * Read the Trapp username from the constant 'WA_TRAPP_USERNAME'.
     *
     * @param  string $user The Trapp username.
     *
     * @return string $user.
     */
    public static function serviceUser($user)
    {
        if (defined('WA_TRAPP_USERNAME')) {
            return WA_TRAPP_USERNAME;
        }

        return $user;
    }

    /**
     * Read the Trapp secret from the constant 'WA_TRAPP_SECRET'.
     *
     * @param  string $secret The Trapp secret.
     *
     * @return string $secret.
     */
    public static function serviceSecret($secret)
    {
        if (defined('WA_TRAPP_SECRET')) {
            return WA_TRAPP_SECRET;
        }

        return $secret;
    }

    /**
     * Check if the 'APP_ENV' is found and is not production.
     *
     * @param  bool   $isDevelopment If the development should be true.
     *
     * @return string $isDevelopment.
     */
    public static function serviceDevelopment($isDevelopment) {
        if (!defined('APP_ENV')) {
            return $isDevelopment;
        }

        if (APP_ENV == 'production') {
            return false;
        }

        return true;
    }

    /**
     * Read the Trapp app code from the constant 'WA_APP_CODE'.
     *
     * @param  string $app_code The Trapp app code.
     *
     * @return string $app_code.
     */
    public static function saveAppCode($app_code)
    {
        if (defined('WA_APP_CODE')) {
            return WA_APP_CODE;
        }

        return $app_code;
    }

    /**
     * Read the Trapp brand code from the constant 'WA_BRAND_CODE'.
     *
     * @param  string $brand_code The Trapp brand code.
     *
     * @return string $brand_code.
     */
    public static function saveBrandCode($brand_code)
    {
        if (defined('WA_BRAND_CODE')) {
            return WA_BRAND_CODE;
        }

        return $brand_code;
    }
}
