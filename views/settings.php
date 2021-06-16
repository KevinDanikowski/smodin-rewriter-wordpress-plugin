<div id="sr-settings">

	<h2><?php _e( 'Settings', 'smodinrewriter' ); ?></h2>

	<?php if ( $this->notice ) { ?>
		<div class="updated"><p><?php echo esc_html( $this->notice ); ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="error"><p><?php echo esc_html( $this->error ); ?></p></div>
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
					<p class="description"><?php echo sprintf( __( 'Need an API key? Get one %shere%s', 'smodinrewriter'), '<a href="https://rapidapi.com/smodin/api/rewriter-paraphraser-text-changer-multi-language" target="_new">', '</a>' ); ?></p>
				</div>
				<div class="sr-form-group">
					<label for="lang"><?php echo __( 'Language', 'smodinrewriter' ); ?></label>
					<select name="lang">
					<?php
					$default_lang = SmodinRewriter_Util::get_option( 'lang' );
					foreach ( $languages as $symbol => $lang ) {
						?>
						<option value="<?php echo $symbol; ?>" <?php echo $symbol === $default_lang ? 'selected' : ''; ?>><?php echo sprintf( '%s (%s)', $lang['language'], $lang['nativeName'] ); ?></option>
						<?php
					}
					?>
					</select>
				</div>
				<div class="sr-form-group">
					<label for="strength"><?php echo __( 'Strength', 'smodinrewriter' ); ?></label>
					<select name="strength">
					<?php
					$default_strength = SmodinRewriter_Util::get_option( 'strength' );
					if ( empty( $default_strength ) ) {
						$default_strength = 3;
					}
					foreach ( $strength as $weight ) {
						?>
						<option value="<?php echo $weight; ?>" <?php echo $weight === intval( $default_strength ) ? 'selected' : ''; ?>><?php echo $weight; ?></option>
						<?php
					}
					?>
					</select>
				</div>
			</div>

			<?php wp_nonce_field( SMODINREWRITER_SLUG, 'nonce' ); ?>

		</div>

		<div class="sr-settings-submit">
			<input type="submit" class="button button-primary" id="sr-settings-button" name="sr-settings-button" value="<?php _e( 'Save Settings', 'smodinrewriter' ); ?>">
		</div>

	</form>
</div>

