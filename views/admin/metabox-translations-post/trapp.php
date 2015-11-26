<div class="form-wrap">
    <h4><?php _e('TRAPP', $text_domain); ?></h4>

    <div class="form-field">
        <label for="trapp-comment">
            <strong><?php _e('Comment:', $text_domain); ?></strong>
        </label>
        <textarea name="trapp_comment" id="trapp-comment"></textarea>
    </div>

    <div class="form-field">
        <label for="trapp-deadline">
            <strong><?php _e('Deadline:', $text_domain); ?></strong>
        </label>
        <input type="text" id="trapp-deadline" value="<?php echo date('Y-m-d', current_time('timestamp')); ?>">
    </div>

    <div class="form-field">
        <label for="trapp-start">
            <input type="checkbox" id="trapp-start" value="1">
            <strong><?php _e('Begin translation right away', $text_domain); ?></strong>
        </label>
        <p class="description">
            <?php _e('By checking this box and pressing "Send to translation" you are telling translators in TRAPP not to wait. Sometimes you might want to copy+paste already translated PDF-material into articles that are sent to TRAPP, if so, then uncheck this box and press "Send to translation"."', $text_domain)); ?>
        </p>
    </div>

    <?php submit_button(__('Send to translation', $text_domain), 'primary large', 'send_to_trapp'); ?>

</div>
