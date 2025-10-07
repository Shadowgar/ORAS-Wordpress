<ul class="reminder_actions submitbox">

<?php do_action( $prefix . '_reminder_actions_start', $post_id ); ?>

	<li class="wide" id="actions">
		<label for="<?php echo esc_attr( $prefix ); ?>_reminder_action"><?php echo esc_html__( 'Status: ', 'woocommerce-booking' ); ?></label>
		<select name="<?php echo esc_attr( $prefix ); ?>_reminder_action">
			<?php foreach ( $reminder_actions as $reminder_action => $reminder_title ) { ?>
				<option value="<?php echo esc_attr( $reminder_action ); ?>" <?php echo ( $reminder_action == $reminder_status ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $reminder_title ); ?></option>
			<?php } ?>
		</select>
	</li>

	<li class="wide" style="padding-bottom: 16px;">
		<div id="delete-action">
		<?php
		if ( current_user_can( 'delete_post', $post_id ) ) {

			if ( ! EMPTY_TRASH_DAYS ) {
				$delete_text = __( 'Delete permanently', 'woocommerce-booking' );
			} else {
				$delete_text = __( 'Move to Trash', 'woocommerce-booking' );
			}
			?>
			<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post_id ) ); ?>"><?php echo esc_html( $delete_text ); ?></span></a>
			<?php
		}
		?>
		</div>
		<div id="publishing-action">
			<input type="submit" class="button save_reminder button-primary" name="<?php echo esc_attr( $prefix ); ?>_reminder" value="<?php echo 'auto-draft' === $post->post_status ? esc_attr__( 'Save Settings', 'woocommerce-booking' ) : esc_attr__( 'Update Settings', 'woocommerce-booking' ); ?>">
		</div>
	</li>

<?php do_action( $prefix . '_reminder_actions_end', $post_id ); ?>

</ul>