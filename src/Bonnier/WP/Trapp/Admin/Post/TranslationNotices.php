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
        $has_trapp_key = get_post_meta(get_the_ID(), Events::TRAPP_META_KEY, true);

        if ($is_master || !$has_trapp_key) {
            return;
        }

        $translation_link = get_post_meta(get_the_ID(), Events::TRAPP_META_LINK, true);
        $link_text = sprintf('<a href="%s" target="_blank"><strong>TRAPP</strong></a>', esc_url($translation_link));

        $notice = '<div id="message" class="error notice">';

            $notice .= '<p>';
                $notice .= sprintf('<strong>%s:</strong><br>', __('Warning', Plugin::TEXT_DOMAIN));
                $notice .= sprintf(__('This is a translation found inside the TRAPP service and should get updated in %s instead.', Plugin::TEXT_DOMAIN), $link_text);
            $notice .= '</p>';

        $notice .= '</div>';

        echo $notice;
    }
}
