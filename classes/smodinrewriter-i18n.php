<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 */
class SmodinRewriter_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'smodinrewriter',
			false,
			dirname( SMODINREWRITER_BASENAME ) . '/languages/'
		);

	}
}
