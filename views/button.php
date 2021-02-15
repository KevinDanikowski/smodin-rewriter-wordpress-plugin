<style>
	.locker,
	.locker-loader {
		position: absolute;
		top: 0;
		left: 0;
	}

	.locker {
		z-index: 1000;
		opacity: 0.8;
		background-color: white;
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
		filter: alpha(opacity=80);
	}

	.locker-loader {
		z-index: 1001;
		background: url('<?php echo includes_url( 'images/wpspin.gif' ); ?>' ) no-repeat center center;
	}

	#smodinrewriter {
		margin: 5px auto;
		border: 1px inset grey;
		box-shadow: inset 0 0 5px grey;
		border-radius: 4px;
		padding: 15px;
		width: 80%;
		display: flex;
		flex-wrap: wrap;
		flex-flow: column;
	}

	#smodinrewriter > #smodinrewriter-button {
	}

	#smodinrewriter-modal, .smodinrewriter-section {
		display: none;
	}

	.smodinrewriter-confirm, .smodinrewriter-error {
		text-align: center;
		padding: 5px;
	}

	.smodinrewriter-confirm .smodinrewriter-prewrite-message {
		font-size: larger;
	}

	.smodinrewriter-error {
		color: #ff0000;
	}

	.smodinrewriter-noclose .ui-dialog-titlebar-close {
		display: none;
	}

	.smodinrewriter-prewrite-disclaimer {
		margin-bottom: 10px;
		background-color: #ececec;
	}

	button.smodinrewriter-link, button.smodinrewriter-link:hover {
		background: none!important;
		border: none;
		padding: 0!important;
		/*input has OS specific font-family*/
		color: #069;
		text-decoration: underline;
		cursor: pointer;
		box-shadow: none;
		width: auto !important;
	}

</style>

<div id="smodinrewriter">
	<select name="lang" id="smodinrewriter-lang">
	<?php
	foreach ( $languages as $symbol => $lang ) {
		?>
		<option value="<?php echo $symbol; ?>" <?php echo $symbol === $post_lang ? 'selected' : ''; ?>><?php echo sprintf( '%s (%s)', $lang['language'], $lang['nativeName'] ); ?></option>
		<?php
	}
	?>
	</select>

	<select name="strength" id="smodinrewriter-strength">
		<option value="0"><?php _e( 'Strength', 'smodinrewriter' ); ?></option>
	<?php
	foreach ( $strength as $weight ) {
		?>
		<option value="<?php echo $weight; ?>" <?php echo $weight === intval( $post_strength ) ? 'selected' : ''; ?>><?php echo $weight; ?></option>
		<?php
	}
	?>
	</select>

	<input type="button" class="button button-primary" value="<?php echo sprintf( __( 'Rewrite using %s', 'smodinrewriter' ), SMODINREWRITER_SHORT_NAME ); ?>" id="smodinrewriter-button">
</div>

<div id="smodinrewriter-modal" class="smodinrewriter-dialog" title="<?php echo sprintf( __( 'Rewrite using %s', 'smodinrewriter' ), SMODINREWRITER_SHORT_NAME ); ?>">
	<div class="smodinrewriter-error smodinrewriter-section"></div>
	<div class="smodinrewriter-success smodinrewriter-section">
		<?php wp_editor( '', 'smodinrewriter-rewritten', array( 'media_buttons' => false, 'teeny' => true, 'tinymce' => false, 'quicktags' => false ) ); ?>
	</div>
	<div class="smodinrewriter-confirm smodinrewriter-section">
		<div class="smodinrewriter-prewrite-disclaimer"><?php esc_html_e( 'Rarely HTML can break using the rewrite functionality and you may need to make manual adjustments afterwards. We recommend not including script, select, or complicated tag structure for best results.', 'smodinrewriter' ); ?></div>
		<div class="smodinrewriter-prewrite-message"></div>
	</div>

	<button style="display: none" id="smodinrewriter-clipboard"></button>
</div>
