<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Smodin Rewriter
 * Plugin URI:        https://smodin.me/services
 * Description:       Smodin Rewriter
 * Version:           1.0.0
 * Author:            Smodin
 * Author URI:        https://smodin.me/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smodinrewriter
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * The code that runs during plugin activation.
 */
function activate_smodinrewriter() {
	SmodinRewriter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smodinrewriter() {
	SmodinRewriter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smodinrewriter' );
register_deactivation_hook( __FILE__, 'deactivate_smodinrewriter' );

/**
 * The function that will handle the queue for autoloader.
 */
function smodinrewriter_autoload( $class ) {
	$namespaces = array( 'SmodinRewriter' );
	foreach ( $namespaces as $namespace ) {
		if ( substr( $class, 0, strlen( $namespace ) ) === $namespace ) {
			$filename = plugin_dir_path( __FILE__ ) . 'classes/' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			if ( is_readable( $filename ) ) {
				require_once $filename;

				return true;
			}
			$filename = plugin_dir_path( __FILE__ ) . 'classes/admin/' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			if ( is_readable( $filename ) ) {
				require_once $filename;

				return true;
			}
			$filename = plugin_dir_path( __FILE__ ) . 'classes/public/' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			if ( is_readable( $filename ) ) {
				require_once $filename;

				return true;
			}
			$filename = plugin_dir_path( __FILE__ ) . 'classes/util/' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			if ( is_readable( $filename ) ) {
				require_once $filename;

				return true;
			}
		}
	}
	return false;
}

/**
 * Begins execution of the plugin.
 */
function run_smodinrewriter() {
	define( 'SMODINREWRITER_BASEFILE', __FILE__ );
	define( 'SMODINREWRITER_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'SMODINREWRITER_BASENAME', plugin_basename( __FILE__ ) );
	define( 'SMODINREWRITER_ABSPATH', dirname( __FILE__ ) );
	define( 'SMODINREWRITER_SLUG', 'smodinrewriter' );
	define( 'SMODINREWRITER_NAME', 'Smodin Rewriter' );
	define( 'SMODINREWRITER_SHORT_NAME', 'Smodin' );
	define( 'SMODINREWRITER_VERSION', '1.0.0' );
	define( 'SMODINREWRITER_EMAILID', 'customer@app-translation.com' );

	// make this false when releasing.
	define( 'SMODINREWRITER_DEBUG', false );

	$plugin = SmodinRewriter::instance();
	$plugin->run();
	$vendor_file = SMODINREWRITER_ABSPATH . '/vendor/autoload.php';
	if ( is_readable( $vendor_file ) ) {
		require_once $vendor_file;
	}
}

spl_autoload_register( 'smodinrewriter_autoload' );
run_smodinrewriter();

