<div id="sr-settings">

	<h2><?php _e( 'Settings', 'smodinrewriter' ); ?></h2>

	<?php if ( $this->notice ) { ?>
		<div class="updated"><p><?php echo $this->notice; ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="error"><p><?php echo $this->error; ?></p></div>
	<?php } ?>

	<form method="post" action="">
		<div id="sr-settings-tabs" class="nav-tab-wrapper">
			<ul>
				<li><a href="#tab-general" class="sr-tab"><?php _e( 'General', 'smodinrewriter' ); ?></a></li>
			</ul>

			<div id="tab-general">
				<div class="sr-form-group">
					<label for="apikey"><?php echo __( 'API key', 'smodinrewriter' ); ?></label>
					<input id="apikey" type="password" class="sr-form-control regular-text" name="apikey" value="<?php echo esc_attr( SmodinRewriter_Util::get_option( 'apikey' ) ); ?>" />
					<p class="description"><?php echo sprintf( 'Need an API key? Get one %shere%s', '<a href="https://rapidapi.com/smodin/api/rewriter-paraphraser-text-changer-multi-language" target="_new">', '</a>' ); ?></p>
				</div>
			</div>

			<?php wp_nonce_field( SMODINREWRITER_SLUG, 'nonce' ); ?>

		</div>

		<div class="sr-settings-submit">
			<input type="submit" class="button button-primary" id="sr-settings-button" name="sr-settings-button" value="<?php _e( 'Save Settings', 'smodinrewriter' ); ?>">
		</div>

	</form>
</div>

