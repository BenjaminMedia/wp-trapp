<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Core\Mappings;
use Bonnier\WP\Trapp\Admin\Post\Events;

class TranslationNotices
{
    public function registerNotice()
    {
        $postType = get_post_type();

        if (!in_array($postType, Mappings::postTypes())) {
            return;
        }

        $is_master = get_post_meta(get_the_ID(), Events::TRAPP_META_MASTER, true);

        if ($is_master) {
            return;
        }

        $fieldGroups = Mappings::getFields($postType);
        $translation_link = get_post_meta(get_the_ID(), Events::TRAPP_META_LINK, true);
        $link_text = sprintf('<a href="%s" target="_blank"><strong>TRAPP</strong></a>', esc_url($translation_link));

        $notice = '<div id="message" class="error notice">';

            $notice .= '<p>';
                $notice .= sprintf('<strong>%s:</strong><br>', __('Warning', Plugin::TEXT_DOMAIN));
                $notice .= sprintf(__('This is a translation found inside the TRAPP service and should get updated in %s instead.', Plugin::TEXT_DOMAIN), $link_text);
                $notice .= '<br>';
                $notice .= sprintf(__('These fields cannot be updated inside of this application:', Plugin::TEXT_DOMAIN), $link_text);
            $notice .= '</p>';

            $notice .= '<p>';
                $notice .= '<ul>';
                    foreach ($fieldGroups as $fieldGroup) {
                        foreach ($fieldGroup['fields'] as $field ) {
                            $notice .= sprintf( '<li><strong>- %s</strong></li>', $field['label'] );
                        }
                    }
                $notice .= '</ul>';
            $notice .= '</p>';

        $notice .= '</div>';

        echo $notice;
    }
}
