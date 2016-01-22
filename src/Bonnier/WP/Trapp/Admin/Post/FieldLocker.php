<?php

namespace Bonnier\WP\Trapp\Admin\Post;

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
