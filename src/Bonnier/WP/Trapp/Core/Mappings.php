<?php
namespace Bonnier\WP\Trapp\Core;

use Bonnier\Trapp\Translation\TranslationField;

class Mappings
{

    /**
     * Sets credentials from WP filters.
     */
    public static function postTypes()
    {
        return apply_filters('bp_trapp_post_types', [] );
    }

    public static function getFields($postType = 'post') {
        $fields = self::getDefaultFields();

        // Remove post_thumbnail if the post type does not support it
        if ( isset( $fields['post_thumbnail'] ) && !post_type_supports($postType, 'thumbnail')) {
            unset($fields['post_thumbnail']);
        }

        $hook = sprintf('bp_trapp_get_%s_fields', $postType);

        return apply_filters($hook, $fields );
    }

    public static function getDefaultFields() {
        $defaultFields = [
            'post' => [
                'title' => 'Post',
                'fields' => [
                    'title' => [
                        'label' => 'Post Title',
                        'args' => [
                            'key' => 'post_title'
                        ],
                        'type' => 'wp_post',
                    ],
                    'body' => [
                        'label' => 'Post Body',
                        'args' => [
                            'key' => 'post_content'
                        ],
                        'type' => 'wp_post',
                    ],
                ],
            ],
            'post_thumbnail' => [
                'title' => 'Post Thumbnail',
                'fields' => [
                    'title' => [
                        'label' => 'Post Thumbnail Title',
                        'args' => [
                            'image_key' => '_thumbnail_id',
                            'key' => 'post_title'
                        ],
                        'type' => 'image_wp_post',
                    ],
                    'alt' => [
                        'label' => 'Post Thumbnail Alt',
                        'args' => [
                            'image_key' => '_thumbnail_id',
                            'key' => '_wp_attachment_image_alt'
                        ],
                        'type' => 'image_post_meta',
                    ]
                ]
            ],
        ];

        return apply_filters('bp_trapp_default_fields', $defaultFields);
    }

    public static function translationField($field, $postId, $post) {
        $value = self::getValue($field['type'], $postId, $post, $field['args']);

        if (!$value) {
            return false;
        }

        if (is_array($value)) {
            $translationField = [];

            foreach ($value as $key => $singleValue) {
                $translationSingleField = new TranslationField($key, $singleValue);
                $translationSingleField->setGroup($field['group']);

                $translationField[] = $translationSingleField;
            }
        } else {
            $translationField = new TranslationField($field['label'], $value);
            $translationField->setGroup($field['group']);
        }

        return $translationField;
    }

    public static function getValue($type, $postId, $post, $args = array()) {
        $hook = sprintf('bp_trapp_get_%s_value', $type);

        return apply_filters($hook, '', $postId, $post, $args);
    }

    public static function updateValue($type, $post, $value, $args = array()) {
        $hook = sprintf('bp_trapp_update_%s_value', $type);

        return apply_filters($hook, false, $post, $value, $args);
    }
}
