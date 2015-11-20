<label for"trapp-comment"><?php _e('Comment'); ?></label>
<textarea name="trapp_comment" id="trapp-comment"></textarea>

<label for"trapp-deadline"><?php _e('Deadline'); ?></label>
<input type="text" value="<?php echo date('Y-m-d H:i:s', current_time('timestamp')); ?>">

<?php submit_button(__('Send to TRAPP'), 'primary', 'send_to_trapp'); ?>
