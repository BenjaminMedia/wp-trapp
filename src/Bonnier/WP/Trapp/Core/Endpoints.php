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
            #'methods'             => WP_REST_Server::EDITABLE,
            'methods'             => WP_REST_Server::READABLE,
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
        $this->htmlHeader();

        $request = $this->getFromCallback();
        $trappId = $request->getId();
        $post = $this->getPostByTrappId($trappId);

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
#        update_option($name, $option);
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

            d($this->updateField($group, $label, $value, $post));
            ddd('updatefield');
        }

        $response = new WP_REST_Response( ['Success. Post Updated.'], 200 );

        return $response;
    }

    public function updateField($group, $label, $value, $post)
    {
        $fieldGroups = Mappings::getFields(get_post_type($post));

        foreach ($fieldGroups as $groupKey => $fieldGroup) {
            if ($fieldGroup['title'] == $group) {
                $updateGroup = $groupKey;
                break;
            }
        }

        if (!isset($updateGroup)) {
            return false;
        }

        $groupFields = $fieldGroups[$updateGroup]['fields'];

        foreach ($groupFields as $groupField) {
            if ($groupField['label'] == $label) {
                $updateField = $groupField;
                break;
            }
        }

        if (!isset($updateField)) {
            return false;
        }

        return Mappings::updateValue($updateField['type'], $post, $value, $updateField['args']);

        // These should exists as action callbacks
        switch($groupField['type']) {
            case 'wp_post' :
                $updateArgs = [];
                $updateArgs['ID'] = $post->ID;
                $updateArgs[$updateFieldKey] = $value;

                // Update post
                $updatedPostId = wp_update_post($updateArgs, true);

                if ( is_wp_error($updatedPostId)) {
                    return false;
                }

                break;

            case 'post_thumbnail_wp_post' :
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

            case 'post_thumbnail_meta' :
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
    #    $callbacks = $this->translationCallbacks();
    #    $callback = $callbacks[0];
    #    $json = $callback['raw'];

        #$json = file_get_contents('php://input');

        $json = '{"deadline":"2016-01-20 00:00:00","original_entity_id":"569fa487c01443a6068b4619","title":"Test med masser af data","update_endpoint_uri":"http:\/\/bp-product-search.dev\/wp-json\/bp\/trapp\/v1\/update_translation","app_code":"productsearch","brand_code":"dif","updated_at":"2016-01-20 15:15:54","created_at":"2016-01-20 15:15:19","fields":[{"label":"Post Title","value":"Test med masser af data (svupdate)","display_format":"text","group":"Post","shared_key":"ee2c63282b4b61578e7b7a1a69f0ddbc"},{"label":"Post Body","value":"Tester(svupdate)\r\n\r\nMed body","display_format":"text","group":"Post","shared_key":"39b796c7ce17a7c967cf06a969be7520"},{"label":"Product Image Title","value":"Da title(svupdate)","display_format":"text","group":"Product Image","shared_key":"d4f223d2691642b116fe94398c54ef8f"},{"label":"Product Image Alt","value":"Da alt(svupdate)","display_format":"text","group":"Product Image","shared_key":"42305a8d6b0a14e258f17e013239171a"},{"label":"Featured Image Title","value":"DK title(svupdate)","display_format":"text","group":"Featured Image","shared_key":"6c7d1814f57e0252d433d3a89302c23b"},{"label":"Featured Image Alt","value":"DK text(svupdate)","display_format":"text","group":"Featured Image","shared_key":"8d4e5165100d819844999e42ec6349cc"},{"label":"Model","value":"Model 1234(svupdate)","display_format":"text","group":"Product information","shared_key":"ec02421454d306bbf311804c03f14cf3"},{"label":"Price","value":"5500(svupdate)","display_format":"text","group":"Product information","shared_key":"3426b9c93229b0033fcf5364361c0085"},{"label":"Description","value":"En beskrivelse her(svupdate)\r\n- En\r\n- To\r\n- Tre","display_format":"text","group":"Product information","shared_key":"9902e686e10e9eba71f1832fc87f3568"},{"label":"Conclusion","value":"Konklusion her(svupdate)\r\n- En\r\n- To\r\n- Tre","display_format":"text","group":"Product information","shared_key":"01d454585fada1b21479785ce3d7a673"},{"label":"Teaser title","value":"En teaser title(svupdate)","display_format":"text","group":"Teaser","shared_key":"196d09dccf05bac2ee21ff5fc1ccce40"},{"label":"Teaser text","value":"En teaser tekst her(svupdate)","display_format":"text","group":"Teaser","shared_key":"69b1479600926b62c1acad19982da05b"}],"comment":null,"state":"state-translated","locale":"sv_se","edit_uri":"http:\/\/localhost\/translation\/569fa487c01443a6068b4627\/edit","related_translations":[{"id":"569fa487c01443a6068b4619","locale":"da_dk","is_original":true}],"id":"569fa487c01443a6068b4627"}';

        $request = ServiceTranslation::fromCallback('', '', $json);

        return $request;
    }

    public function htmlHeader() {
        header('Content-Type: text/html');
    }
}
