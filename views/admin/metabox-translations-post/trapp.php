<div class="form-wrap">
    <h4><?php _e('TRAPP', $text_domain);?></h4>

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
            <strong><?php _e('Start translation?', $text_domain); ?></strong>
        </label>
        <p class="description">
            <?php printf(__('This will set the TRAPP status to %s instead of %s.', $text_domain), '<code>Missing</code>', '<code>On Hold</code>'); ?>
        </p>
    </div>

    <?php submit_button(__('Send to TRAPP', $text_domain), 'primary large', 'send_to_trapp'); ?>

</div>
