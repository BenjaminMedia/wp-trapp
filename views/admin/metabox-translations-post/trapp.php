<div class="form-wrap">
    <h4><?php _e('TRAPP', $text_domain); ?></h4>

    <div class="form-field">
        <label for="trapp-comment">
            <strong><?php _e('Comment:', $text_domain); ?></strong>
        </label>
        <input type="text" name="trapp_comment" id="trapp-comment" name="trapp_comment">
    </div>

    <div class="form-field">
        <label for="trapp-deadline">
            <strong><?php _e('Deadline:', $text_domain); ?></strong>
        </label>
        <input type="text" id="trapp-deadline" name="trapp_deadline" value="<?php echo $deadline; ?>">
    </div>

    <div class="form-field">
        <label for="trapp-start">
            <input type="checkbox" id="trapp-start" name="trapp_start" value="1">
            <strong><?php _e('Begin translation right away', $text_domain); ?></strong>
        </label>
        <p class="description">
            <?php _e('By checking this box and pressing "Send to translation" you are telling translators in TRAPP not to wait.', $text_domain); ?>
            <br>
            <?php _e('Sometimes you might want to copy+paste already translated PDF-material into articles that are sent to TRAPP, if so, then uncheck this box and press "Send to translation".', $text_domain); ?>
        </p>
    </div>

    <?php submit_button(__('Send to translation', $text_domain), 'primary large', 'send_to_trapp'); ?>

</div>
