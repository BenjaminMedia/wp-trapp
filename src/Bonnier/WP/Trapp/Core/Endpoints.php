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
            'callback'            => array( $this, 'updateTrapp' ),
            #'permission_callback' => array( $this, 'updateTrappPermissions' ),
        ]);
        register_rest_route($namespace, '/translation_callbacks', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'translationCallbacks' ),
        ]);
        register_rest_route($namespace, '/translation_callbacks_raw', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'translationCallbacksRaw' ),
        ]);
    }

    public function getNameSpace() {
        return sprintf('%s/v%d', self::PREFIX, self::VERSION);
    }

    public function updateTrapp($request)
    {
        $name = 'bp_trapp_test_callback';
        $option = get_option($name, []);
        $entry = [
            'request' => $request,
            'post' => $_POST,
            'raw' => file_get_contents('php://input'),
        ];

        array_unshift($option, $entry);
        update_option($name, $option);

        $response = new WP_REST_Response( ['Callback saved.'], 200 );

        return $response;

        $request = $this->getRequest('sv');

        $trappId = $request->getId();
        $post = $this->getPostByTrappId($trappId);

        if (!$post) {
            return false; // Or like "Post not found"
        }

        $is_master = get_post_meta($post->ID, Events::TRAPP_META_MASTER, true );

        if ($is_master) {
            return false; // Or like "Master translation cannot get updated"
        }

        $fields = $request->getFields();

        foreach ($fields as $field) {
            $group = $field->getGroup();
            $label = $field->getLabel();

            $this->updateField($group, $label, $post->ID);
        }

        $response = new WP_REST_Response( ['Success. Post Updated.'], 200 );

        return $response;
    }

    public function translationCallbacks()
    {
    #    $this->htmlHeader();
    #    ddd(get_option('bp_trapp_test_callback', array()));
        return get_option('bp_trapp_test_callback', array());
    }

    public function translationCallbacksRaw()
    {
        return get_option('bp_trapp_test_callback_raw', array());
    }

    public function updateField($group, $label, $post_id)
    {
        $group = strtolower($group);

        // TODO Filter here for mappings
        $translationGroups = [];
        $translationGroups['post'] = [
            'post_title' => 'Title',
            'post_name' => 'Name/Slug',
            'post_content' => 'Body',
        ];

        if (!array_key_exists($group, $translationGroups)) {
            return false;
        }

        $groupFields = $translationGroups[$group];
        $updateFieldKey = array_search($label, $groupFields);

        if ($updateFieldKey === false) {
            return false;
        }

        $updateFieldValue = $groupFields[$updateFieldKey];

        // Each $groupFields should have its own callback when mapping instead of this custom one

        $update_args = [];
        $update_args['ID'] = $post_id;
        $update_args[$updateFieldKey] = $updateFieldValue;

        // Update post
        $updated_post_id = wp_update_post( $update_args, true );
/*
        // Return errors whenever we handle the return
		if ( is_wp_error( $updated_post_id ) ) {
			if ( in_array( $updated_post_id->get_error_code(), array( 'db_update_error' ) ) ) {
				$updated_post_id->add_data( array( 'status' => 500 ) );
			} else {
				$updated_post_id->add_data( array( 'status' => 400 ) );
			}

			return $updated_post_id;
		}
*/
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
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function updateTrappPermissions($request )
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Check if the posted username and secret matches what's in the app.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        $service = new ServiceTranslation;
        $request = $this->getRequest()->getService();

        if ( $service->getUserName() == $request->getUserName() && $service->getSecret() == $request->getSecret() ) {
            return true;
        }

        return false;
    }

    public function getRequest($translation = '') {
        $request = new ServiceTranslation;

        switch ($translation) {
            case 'no':
                $id = '567136b0c014436f638b456c';
                break;
            case 'sv':
                $id = '567136b0c014436f638b4571';
                break;
            case 'master':
            default:
                $id = '567136b0c014436f638b4567';
        }

        return $request->getById($id);
    }

    public function htmlHeader() {
        header('Content-Type: text/html');
    }
}
