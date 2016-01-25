<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp;
use Bonnier\WP\Trapp\Core\Mappings;

class FieldLocker
{
    public function filterInsertPostData($data)
    {
        if ( ! $this->isTranslation() ) {
            return $data;
        }

        $fieldGroups = Mappings::getFields(get_post_type());

        foreach ($fieldGroups as $fieldGroup) {
            foreach ($fieldGroup['fields'] as $field ) {
                if ( $field['type'] != 'wp_post' ) {
                    continue;
                }

                $key = $field['args']['key'];

                if ( array_key_exists($key, $data) ) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    public function setLockedFields() {
        if ( ! $this->isTranslation() ) {
            return;
        }

        $fields = [];

        $fieldGroups = Mappings::getFields(get_post_type());

        foreach ($fieldGroups as $fieldGroup) {
            foreach ($fieldGroup['fields'] as $field ) {
                $lockedField = apply_filters('bp_trapp_locked_field', '', $field);

                if (!empty($lockedField)) {
                    $fields[] = trim($lockedField);
                }
            }
        }

        $fields = array_unique($fields);

        printf('<div id="bp-trapp-locked-fields" data-fields="%s"></div>', implode(' ', $fields));
    }

    public function enqueueLockFields() {
        if ( ! $this->isTranslation() ) {
            return;
        }

        $src = Trapp\instance()->plugin_url . 'js/bp-trapp-lock-fields.js';
        $deps = [
            'jquery'
        ];

        wp_enqueue_script('bp-trapp-metabox', $src, $deps);
    }

    public function filterLockedFields($return, $field) {
        switch($field['type']) {
            case 'post_meta':
                $return = $field['args']['key'];
                break;

            case 'wp_post':
                if ($field['args']['key'] == 'post_title') {
                    $return = $field['args']['key'];
                } elseif ($field['args']['key'] == 'post_content') {
                    add_filter('tiny_mce_before_init', [$this, 'disableEditorInit']);
                    add_filter('wp_editor_settings', [$this, 'disableEditorRich']);
                }
        }

        return $return;
    }

    public function disableEditorInit($args) {
        $args['readonly'] = 1;

        return $args;
    }

    public function disableEditorRich($settings) {
        $settings['media_buttons'] = false;
        $settings['quicktags'] = false;

        return $settings;
    }

    public function filterUpdatePostMetadata($check, $objectId, $metaKey)
    {
        if ( ! $this->isTranslation() ) {
            return $check;
        }

        if ( $this->getFieldByType('post_meta', $metaKey ) ) {
            return false;
        }

        return $check;
    }

    public function getFieldByType($type, $key) {
        $fieldGroups = Mappings::getFields(get_post_type());

        foreach ($fieldGroups as $fieldGroup) {
            foreach ($fieldGroup['fields'] as $field ) {
                if ( $field['type'] != $type ) {
                    continue;
                }

                if ($field['args']['key'] == $key ) {
                    return $key;
                }
            }
        }

        return false;
    }

    public function isTranslation() {
        $postType = get_post_type();

        if (!in_array($postType, Mappings::postTypes())) {
            return false;
        }

        $is_master = get_post_meta(get_the_ID(), Events::TRAPP_META_MASTER, true);
        $has_trapp_key = get_post_meta(get_the_ID(), Events::TRAPP_META_KEY, true);

        if (!$is_master && $has_trapp_key) {
            return true;
        }

        return false;
    }
}
