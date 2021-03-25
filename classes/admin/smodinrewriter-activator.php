<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 */
class SmodinRewriter_Activator {

	/**
	 * Plugin activation action.
	 *
	 * Triggers the plugin activation action on plugin activate.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function activate() {
		add_option( 'smodinrewriter-activated', true );
		add_option( 'smodinrewriter-temp-activated', true );
	}


}
