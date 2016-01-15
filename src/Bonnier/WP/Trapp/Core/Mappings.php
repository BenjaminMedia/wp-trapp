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
                    'label' => 'Post Title',
                    'value' => $post->post_title,
                ],
                'post_content' => [
                    'label' => 'Post Body',
                    'value' => $post->post_content,
                ]
            ]
        ];

        if (has_post_thumbnail($postId)) {
            $thumbnailId = get_post_thumbnail_id($postId);
            $thumbnailPost = get_post($thumbnailId);

            $translationGroups['post_thumbnail'] = [
                'title' => 'Post Thumbnail',
                'fields' => [
                    'post_title' => [
                        'label' => 'Post Thumbnail Title',
                        'value' => $thumbnailPost->post_title,
                    ],
                ]
            ];

            $alt = get_post_meta($thumbnailId, '_wp_attachment_image_alt', true);
            if ($alt) {
                $translationGroups['post_thumbnail']['fields']['alt'] = [
                    'label' => 'Post Thumbnail Alt',
                    'value' => $alt,
                ];
            }
        }

        return apply_filters('bp_trapp_translation_groups', $translationGroups, $postId, $post);
    }
}
