<?php
namespace Bonnier\WP\Trapp\Core;

class Mappings
{

    /**
     * Sets credentials from WP filters.
     */
    public static function postTypes()
    {
        return apply_filters('bp_trapp_post_types', [] );
    }

    public static function translationGroup($postId, $post) {
        $translationGroups = [];

        $translationGroups['post'] = [
            'title' => 'Post',
            'fields' => [
                'post_title' => [
                    'label' => 'Title',
                    'value' => $post->post_title,
                ],
                'post_content' => [
                    'label' => 'Body',
                    'value' => $post->post_content,
                ]
            ]
        ];

        return apply_filters('bp_trapp_translation_groups', $translationGroups, $postId, $post);
    }
}
