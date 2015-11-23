<div id="post-translations" class="translations">

	<h4><?php _e('Translations', $text_domain);?></h4>

	<table>
		<?php foreach ($languages as $language) : ?>

		<?php
			$value = $polylang->model->get_translation('post', $post_id, $language);
			if (!$value || $value == $post_id) // $value == $post_id happens if the post has been (auto)saved before changing the language
				$value = '';
			if (isset($_GET['from_post']))
				$value = $polylang->model->get_post((int)$_GET['from_post'], $language);

			$link = $add_link = sprintf(
				'<a href="%1$s" class="pll_icon_add" title="%2$s"></a>',
				esc_url($polylang->links->get_new_post_translation_link($post_id, $language)),
				__('Add new', $text_domain)
			);

			if ($value) {
				$selected = get_post($value);
				$link = $polylang->edit_translation_link($value);
			} ?>

			<tr>
				<td class="pll-language-column"><?php echo $language->flag ? $language->flag : esc_html($language->slug); ?></td>
				<td class="hidden"><?php echo $add_link;?></td>
				<td class="pll-edit-column"><?php echo $link;?></td>
				<td class="pll-translation-column"><?php
					printf('
						<input type="hidden" name="post_tr_lang[%1$s]" id="htr_lang_%1$s" value="%2$s"/>
						<input type="text" class="tr_lang" id="tr_lang_%1$s" value="%3$s">',
						esc_attr($language->slug),
						empty($value) ? 0 : esc_attr($selected->ID),
						empty($value) ? '' : esc_attr($selected->post_title)
					); ?>
				</td>
			</tr>

		<?php endforeach; ?>
	</table>

</div>
