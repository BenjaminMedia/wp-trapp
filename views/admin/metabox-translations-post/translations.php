<div id="post-translations" class="translations">

    <h4><?php _e('Translations', $text_domain);?></h4>

    <table>

        <?php foreach ($languages as $language) : ?>

            <?php

            $value = $polylang->model->get_translation('post', $post_id, $language);

            if (!$value || $value == $post_id) { // $value == $post_id happens if the post has been (auto)saved before changing the language
                $value = '';
            }

            if (isset($_GET['from_post'])) {
                $value = $polylang->model->get_post((int)$_GET['from_post'], $language);
            }

            $link = $add_link = sprintf(
                '<a href="%1$s" class="pll_icon_add" title="%2$s"></a>',
                esc_url($polylang->links->get_new_post_translation_link($post_id, $language)),
                __('Add new', $text_domain)
            );

            //if ($value) {
                $app_link = $polylang->filters_post->edit_translation_link($value);
                $trapp_uri = get_post_meta($value, $trapp_link_key, true);
                //$trapp_link = sprintf('<a class="button button-primary" href="%1$s">%2$s</a>', esc_url($trapp_uri), __('Edit in TRAPP', $text_domain));
                $trapp_link = sprintf('<a class="button" href="%1$s">%2$s</a>', 'aa', __('Edit in TRAPP', $text_domain));
            //}
            ?>

            <?php #if ($value || $is_master || !$has_trapp_key) : ?>

            <tr>
                <td class="pll-language-column"><?php echo $language->flag ? $language->flag : esc_html($language->slug); ?></td>
                <td class="hidden"><?php echo $add_link;?></td>

                <?php #if ($is_master || !$has_trapp_key) : ?>
                    <td class="pll-language-column"><?php
                    printf('
                        <input type="hidden" name="post_tr_lang[%1$s]" id="htr_lang_%1$s" value="1"/>
                        <input type="checkbox" name="trapp_tr_lang[%1$s]" id="htrapp_lang_%1$s" value="1" %2$s %3$s />',
                        esc_attr($language->slug),
                        checked(!empty($value), true, false),
                        disabled(!empty($value), true, false)
                    ); ?>
                    </td>
                <?php #endif; ?>

                    <?php #if ($value) : ?>
                        <td class="pll-edit-column"><?php echo $app_link; ?></td>
                        <td class="pll-language-column"><?php echo $trapp_link; ?></td>
                    <?php #endif; ?>
            </tr>

            <?php #endif; ?>

        <?php endforeach; ?>
    </table>

</div>
