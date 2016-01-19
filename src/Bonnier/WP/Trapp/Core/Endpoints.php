<?php
namespace Bonnier\WP\Trapp\Core;

use Bonnier\WP\Trapp\Core\ServiceTranslation;
use Bonnier\WP\Trapp\Admin\Post\Events;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;

class Endpoints extends WP_REST_Controller
{

    /**
     * The namespace prefix.
     */
    const PREFIX = 'bp/trapp';

    /**
     * The namespace version.
     */
    const VERSION = '1';

    /**
     * The name of the update callback route.
     */
    const ROUTE_UPDATE_CALLBACK = 'update_translation';

    /**
     * Sets credentials from WP filters.
     */
    public function registerRoutes()
    {
        $namespace = $this->getNameSpace();

        register_rest_route($namespace, '/' . self::ROUTE_UPDATE_CALLBACK, [
            'methods'             => WP_REST_Server::EDITABLE,
            //'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'updateTrapp' ),
            'permission_callback' => array( $this, 'updateTrappPermissions' ),
        ]);
        // TODO Remove debug
        register_rest_route($namespace, '/translation_callbacks', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'translationCallbacks' ),
        ]);
    }

    // TODO Remove debug
    public function translationCallbacks()
    {
        return get_option('bp_trapp_test_callback', array());
    }


    public function getNameSpace() {
        return sprintf('%s/v%d', self::PREFIX, self::VERSION);
    }

    public function updateTrapp()
    {
        #$this->htmlHeader();

        $request = $this->getFromCallback();
        $trappId = $request->getId();
        $post = $this->getPostByTrappId($trappId);
        #$post = get_post( 1433 );

        // TODO Remove debug
        $name = 'bp_trapp_test_callback';
        $option = get_option($name, []);
        $entry = [
            'request' => $request,
            'time' => current_time('mysql'),
            'trappID' => $trappId,
            'wpPost' => $post,
            'post' => $_POST,
            'raw' => file_get_contents('php://input'),
        ];
        array_unshift($option, $entry);
        update_option($name, $option);
        // TODO End debug

        if (!$post) {
            return false; // Or like "Post not found"
        }

        $is_master = get_post_meta($post->ID, Events::TRAPP_META_MASTER, true );

        if ($is_master) {
            return false; // Or like "Master translation cannot get updated"
        }

        if ($request->getState() != 'state-translated') {
            return false;
        }

        $fields = $request->getFields();

        foreach ($fields as $field) {
            $group = $field->getGroup();
            $label = $field->getLabel();
            $value = $field->getValue();

            $this->updateField($group, $label, $value, $post);
        }

        $response = new WP_REST_Response( ['Success. Post Updated.'], 200 );

        return $response;
    }

    public function updateField($group, $label, $value, $post)
    {
        $translationGroups = Mappings::translationGroup($post->ID, $post);

        foreach ($translationGroups as $groupKey => $translationGroup) {
            if ($translationGroup['title'] == $group) {
                $updateGroup = $groupKey;
                break;
            }
        }

        if (!isset($updateGroup)) {
            return false;
        }

        $groupFields = $translationGroups[$updateGroup]['fields'];

        foreach ($groupFields as $updateFieldKey => $groupField) {
            if ($groupField['label'] == $label) {
                $updateField = $groupField;
                break;
            }
        }

        if (!isset($updateField)) {
            return false;
        }

        if ($updateFieldKey === false) {
            return false;
        }

        // These should exists as action callbacks
        if ($groupField['type'] == 'wp_post') {
            $updateArgs = [];
            $updateArgs['ID'] = $post->ID;
            $updateArgs[$updateFieldKey] = $value;

            // Update post
            $updatedPostId = wp_update_post($updateArgs, true);

            if ( is_wp_error($updatedPostId)) {
                return false;
            }

        } elseif ($groupField['type'] == 'post_thumbnail_wp_post') {die("IS");
            if (!has_post_thumbnail($post->ID)) {
                return false;
            }

            $updateArgs = [];
            $updateArgs['ID'] = get_post_thumbnail_id($post->ID);
            $updateArgs[$updateFieldKey] = $value;

            $updatedThumbId = wp_update_post($updateArgs, true);

            if (is_wp_error( $updatedThumbId)) {
                return false;
            }

        } elseif ($groupField['type'] == 'post_thumbnail_meta') {
            if (!has_post_thumbnail($post->ID)) {
                return false;
            }

            update_post_meta(get_post_thumbnail_id($post->ID), $updateFieldKey, $value);
        }

        return true;
    }

    public function getPostByTrappId($trappId)
    {
        $args = [
            'post_type' => 'any', // TODO Use filter
            'post_status' => 'any',
            'meta_key' => Events::TRAPP_META_KEY,
            'meta_value' => $trappId,
            'posts_per_page' => 1,
        ];
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->post;
        }

        return false;
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @return WP_Error|bool
     */
    public function updateTrappPermissions()
    {
        $request = $this->getFromCallback();

        return $this->create_item_permissions_check($request);
    }

    /**
     * Check if the posted username and secret matches what's in the app.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check($request) {
        $service = new ServiceTranslation;

        if ( $service->getUserName() == $request->getUserName() && $service->getSecret() == $request->getSecret() ) {
            return true;
        }

        return false;
    }

    public function getFromCallback() {
        // TODO Delete debug
        //$callbacks = $this->translationCallbacks();
        //$callback = $callbacks[0];
        //$json = $callback['raw'];

        $json = file_get_contents('php://input');
        $request = ServiceTranslation::fromCallback('', '', $json);

        return $request;
    }

    public function htmlHeader() {
        header('Content-Type: text/html');
    }
}
