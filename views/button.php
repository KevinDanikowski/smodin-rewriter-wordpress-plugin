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
		background: url('<?php echo includes_url( "images/wpspin.gif" )?>' ) no-repeat center center;
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
		margin: 0px 0px 20px 0px;
	}

	.smodinrewriter-error {
		color: #ff0000;
	}


</style>

<div id="smodinrewriter">
	<select name="lang" id="smodinrewriter-lang">
	<?php
		foreach( $languages as $symbol => $lang ) {
	?>
		<option value="<?php echo $symbol; ?>"><?php echo sprintf( '%s (%s)', $lang['language'], $lang['nativeName'] );?></option>
	<?php
		}
	?>
	</select>

	<select name="strength" id="smodinrewriter-strength">
		<option value="3"><?php _e( 'Strength', 'smodinrewriter' ); ?></option>
	<?php
		foreach( $strength as $weight ) {
	?>
		<option value="<?php echo $weight; ?>"><?php echo $weight;?></option>
	<?php
		}
	?>
	</select>

	<input type="button" class="button button-primary" value="<?php _e( 'Smodin Rewrite', 'smodinrewriter' );?>" id="smodinrewriter-button">
</div>

<div id="smodinrewriter-modal" class="smodinrewriter-dialog" title="<?php _e( 'Smodin Rewrite', 'smodinrewriter' );?>">
	<div class="smodinrewriter-error smodinrewriter-section"></div>
	<div class="smodinrewriter-success smodinrewriter-section">
		<?php wp_editor( '', 'smodinrewriter-rewritten', array( 'media_buttons' => false, 'teeny' => true, 'tinymce' => false, 'quicktags' => false ) ); ?>
	</div>
	<div class="smodinrewriter-confirm smodinrewriter-section">
		<div class="smodinrewriter-prewrite-message"></div>
		<button class="button button-primary" id="smodinrewriter-rewrite"><?php _e( 'Confirm', 'smodinrewriter' ); ?></button>
	</div>
</div>