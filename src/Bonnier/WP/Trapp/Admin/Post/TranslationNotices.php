<?php

namespace Bonnier\WP\Trapp\Admin\Post;

use Bonnier\WP\Trapp\Plugin;
use Bonnier\WP\Trapp\Admin\Post\Events;

class TranslationNotices
{
    public function registerNotice()
    {
        $is_master = get_post_meta(get_the_ID(), Events::TRAPP_META_MASTER, true);

        if ($is_master) {
            return;
        }

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
                    $notice .= '<li><strong>- Title</strong></li>';
                    $notice .= '<li><strong>- Name/Slug</strong></li>';
                    $notice .= '<li><strong>- Body</strong></li>';
                $notice .= '</ul>';
            $notice .= '</p>';

        $notice .= '</div>';

        echo $notice;
    }
}
