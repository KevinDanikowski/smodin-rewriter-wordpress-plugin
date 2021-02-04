<?php
/**
 * All admin hooks.
 *
 * This class defines all code necessary for the admin.
 *
 * @since      1.0.0
 */
class SmodinRewriter_Admin {

	public $notice;
	public $error;

	const MAX_LENGTH = 10000;

	/**
	 * Constructor.
	 *
	 * Creates the instances of this class.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Plugin admin hooks.
	 *
	 * Triggers the admin hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function hooks() {
		add_action( 'admin_menu', array( $this, 'setup_menu' ) );
		add_action( 'wp_ajax_smodinrewriter', array( $this, 'ajax' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'post_submitbox_misc_actions', array( $this, 'show_button' ) );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

	}

    function show_button(){
        global $post, $pagenow;
		
		// show button only if apikey is valid
		if ( ! empty( SmodinRewriter_Util::get_option( 'apikey' ) ) ) {
			$languages = $this->get_languages();
			$strength = array( 1, 2, 3 );
			include_once SMODINREWRITER_ABSPATH . '/views/button.php';
		}
    }

	private function get_languages() {
		$languages = get_transient( 'smodin-languages' );
		if ( ! $languages ) {
			ob_start();
			include SMODINREWRITER_ABSPATH . '/data/data-languages.json';
			$contents = ob_get_clean();
			$langs = json_decode( $contents, true );
			$languages = array();
			foreach ( $langs as $lang ) {
				$languages[ $lang['symbol'] ] = $lang;
			}
			
			// cache languages for 1 month
			set_transient( 'smodin-languages', $languages, MONTH_IN_SECONDS );
		}
		return $languages;
	}

	function enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		add_thickbox();
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'smodinrewriter-admin', SMODINREWRITER_ABSURL . '/assets/js/post.js', array( 'jquery', 'jquery-ui-dialog' ), SMODINREWRITER_VERSION, false );
		wp_localize_script( 'smodinrewriter-admin', 'config', array(
			'ajax' => array(
				'nonce' => wp_create_nonce( SMODINREWRITER_SLUG ),
			),
			'max' => self::MAX_LENGTH,
			'i10n' => array(
				'empty_content' => esc_html__( 'Cannot rewrite empty content.', 'smodinrewriter' ),
				'close_button' => esc_html__( 'Close', 'smodinrewriter' ),
				'publish_button' => esc_html__( 'Publish', 'smodinrewriter' ),
			),
		) );
	}

	function plugin_action_links( $links, $file ) {
		if ( $file === plugin_basename( SMODINREWRITER_BASEFILE ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=' . SMODINREWRITER_SLUG ),
					esc_html__( 'Settings', 'smodinrewriter' )
				)
			);
		}

		return $links;
	}


	function ajax() {
		check_ajax_referer( SMODINREWRITER_SLUG, 'nonce' );

		$return = null;
		switch ( $_POST['_action'] ) {
			case 'pre-rewrite':
				$content = trim( $_POST['content'] );
				$length = mb_strlen( $content );
				
				if ( empty( $content ) ) {
					wp_send_json_error( array( 'msg' => esc_html__( 'Cannot rewrite empty content.', 'smodinrewriter' ) ) );
					break;
				}
				if ( $length > self::MAX_LENGTH ) {
					wp_send_json_error( array( 'msg' => sprintf( esc_html__( 'Content (%d characters) exceeds %d characters.', 'smodinrewriter' ), $length, self::MAX_LENGTH ) ) );
					break;
				}

				$languages = $this->get_languages();
				wp_send_json_success( array( 'count' => $length, 'message' => sprintf( esc_html__( 'Calling the API to rewrite %d characters in %s (%s) with strength %d. Please confirm.', 'smodinrewriter' ), $length, $languages[ $_POST['lang'] ]['language'], $languages[ $_POST['lang'] ]['nativeName'], intval( $_POST['strength'] ) ) ) );
				break;
			case 'rewrite':
				$content = trim( $_POST['content'] );
				SmodinRewriter_Util::log( sprintf( 'Rewriting %s of length %d', $content, strlen( $content ) ), 'debug' );
				$new = SmodinRewriter_Util::call_api( $content, $_POST['lang'], intval( $_POST['strength'] ) );
				wp_send_json_success( array( 'content' => $content, 'rewritten' => $new ) );
				break;
			case 'publish':
				$content = trim( $_POST['content'] );
				break;
		}
	}

	function setup_menu() {
		$svg_base64_icon = '';

		add_menu_page( SMODINREWRITER_NAME, SMODINREWRITER_NAME, 'edit_posts', SMODINREWRITER_SLUG, array( $this, 'render_settings' ), $svg_base64_icon, 80 );
	}

	function render_settings() {
		if ( $_POST && isset( $_POST['sr-settings-button'] ) ) {
			$this->save_settings();
		}

		$wp_scripts = wp_scripts();

		wp_enqueue_script( 'smodinrewriter-settings', SMODINREWRITER_ABSURL . '/assets/js/settings.js', array( 'jquery', 'jquery-ui-tabs' ), SMODINREWRITER_VERSION, false );
		wp_localize_script( 'smodinrewriter-settings', 'config', array() );
		wp_enqueue_style( 'smodinrewriter-admin', SMODINREWRITER_ABSURL . '/assets/css/admin.css', array(), SMODINREWRITER_VERSION, false );
		wp_enqueue_style( 'smodinrewriter-jquery-ui', sprintf( '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', $wp_scripts->registered['jquery-ui-core']->ver ), array( 'smodinrewriter-admin' ), SMODINREWRITER_VERSION );

		if (  ! extension_loaded('mbstring') ) {
			$this->error = sprintf( __( 'The extension %s is not installed or enabled. Please make sure it is installed or rewriting will not work.', 'smodinrewriter' ), '<a href="https://www.php.net/manual/en/mbstring.installation.php" target="_new">mbstring</a>' );
			return;
		}
		include_once SMODINREWRITER_ABSPATH . '/views/settings.php';
	}

	private function save_settings() {
		if ( ! wp_verify_nonce( $_POST['nonce'], SMODINREWRITER_SLUG ) ) {
			wp_die();
		}

		$settings = get_option( 'smodinrewriter-settings' );
		if ( ! $settings ) {
			$settings = array();
		}
		$unset = array( 'nonce', '_wp_http_referer', 'tab', 'sr-settings-button' );

		$config = $_POST;
		foreach ( $unset as $key ) {
			unset( $config[ $key ] );
		}

		delete_option( 'smodinrewriter-settings' );

		// let's check if the API key works
		$result = SmodinRewriter_Util::call_api( 's', 'en', 3, $_POST['apikey' ] );
		if ( is_wp_error( $result ) ) {
			$this->error = sprintf( __( 'Error from API: %s', 'smodinrewriter' ), $result->get_error_message() );
			return;
		}

		$merged = stripslashes_deep( array_merge( $settings, $config ) );
		add_option( 'smodinrewriter-settings', $merged );

		$this->notice = __( 'Settings saved', 'smodinrewriter' );
	}

}
