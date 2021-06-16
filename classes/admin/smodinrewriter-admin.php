<?php
/**
 * All admin hooks.
 *
 * This class defines all code necessary for the admin.
 *
 * @since      1.0.0
 */
class SmodinRewriter_Admin {

	/**
	 * Admin notice.
	 */
	public $notice;

	/**
	 * Admin error notice.
	 */
	public $error;

	/**
	 * Number of characters that can be parsed.
	 */
	const MAX_LENGTH = 10000;

	/**
	 * Default strength.
	 */
	const DEFAULT_STRENGTH = 3;

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
		add_action( 'admin_init', array( $this, 'admin_init' ), 10, 1 );
		add_action( 'wp_ajax_smodinrewriter', array( $this, 'ajax' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'show_button' ) );
		add_action( 'after_setup_theme', array( $this, 'load_dependencies' ), 999 );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

	}

	/**
	 * Load dependencies.
	 */
	function load_dependencies() {
		if ( ! function_exists( 'tgmpa' ) ) {
			include_once SMODINREWRITER_ABSPATH . '/lib/tgmpa/tgm-plugin-activation/class-tgm-plugin-activation.php';
		}

		if ( function_exists( 'tgmpa' ) ) {
			add_action( 'tgmpa_register', array( $this, 'tgmpa_register' ) );
		}
	}

	/**
	 * Initialize TGM.
	 */
	public function tgmpa_register() {
		$plugins = array(
			array(
				'name'     => 'Classic Editor',
				'slug'     => 'classic-editor',
				'required' => true,
			),
		);
		$config  = array(
			'id'           => 'smodinrewriter',
			// Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',
			// Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins',
			// Menu slug.
			'parent_slug'  => 'plugins.php',
			// Parent menu slug.
			'capability'   => 'manage_options',
			// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,
			// Show admin notices or not.
			'dismissable'  => false,
			// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => sprintf( __( '%s will not work without this plugin.', 'smodinrewriter' ), SMODINREWRITER_NAME ),
			// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,
			// Automatically activate plugins after installation or not.
			'message'      => '',
			// Message to output right before the plugins table.
		);
		tgmpa( $plugins, $config );
	}

	/**
	 * Shows the rewrite button in the post edit screen.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function show_button() {
		global $post, $pagenow;

		// show button only if apikey is valid
		if ( ! empty( SmodinRewriter_Util::get_option( 'apikey' ) ) ) {
			$languages = $this->get_languages();
			$strength = $this->get_strengths();

			$post_lang = get_post_meta( $post->ID, 'smodinrewriter-lang', true );
			$post_strength = get_post_meta( $post->ID, 'smodinrewriter-strength', true );

			if ( empty( $post_lang ) ) {
				$post_lang = SmodinRewriter_Util::get_option( 'lang' );
			}
			if ( empty( $post_strength ) ) {
				$post_strength = SmodinRewriter_Util::get_option( 'strength' );
			}

			include_once SMODINREWRITER_ABSPATH . '/views/button.php';
		}
	}


	/**
	 * Gets the supported strengths.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_strengths() {
		return array( 1, 2, 3 );
	}

	/**
	 * Gets the supported languages.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_languages( $specific = null ) {
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

		if ( $specific ) {
			return array_key_exists( $specific, $languages ) ? $languages[ $specific ] : '';
		}
		return $languages;
	}

	/**
	 * Enqueues the scripts and styles required in the post editor screen.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post;

		add_thickbox();
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'smodinrewriter-admin', SMODINREWRITER_ABSURL . '/assets/js/post.js', array( 'jquery', 'jquery-ui-dialog', 'clipboard' ), SMODINREWRITER_VERSION, false );
		wp_localize_script(
			'smodinrewriter-admin', 'config', array(
				'ajax' => array(
					'nonce' => wp_create_nonce( SMODINREWRITER_SLUG ),
				),
				'max' => self::MAX_LENGTH,
				'i10n' => array(
					'empty_content' => esc_html__( 'Cannot rewrite empty content!', 'smodinrewriter' ),
					'close_button' => esc_html__( 'Cancel', 'smodinrewriter' ),
					'publish_button' => esc_html__( 'Publish', 'smodinrewriter' ),
					'draft_button' => esc_html__( 'Save as Draft', 'smodinrewriter' ),
					'confirm_button' => esc_html__( 'Confirm', 'smodinrewriter' ),
					'email_button' => esc_html__( 'Something Broke?', 'smodinrewriter' ),
				),
				'id' => $post->ID,
				'url' => admin_url( 'post.php?action=edit&post=' . $post->ID ),
			)
		);
	}

	/**
	 * Adds the links on the plugin listing screen.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
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


	/**
	 * The single entry point for all AJAX requests.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function ajax() {
		check_ajax_referer( SMODINREWRITER_SLUG, 'nonce' );

		$return = null;
		switch ( $_POST['_action'] ) {
			case 'pre-rewrite':
				$content = wp_filter_post_kses( trim( $_POST['content'] ) );
				$text_only = strip_tags( $content );
				$length = mb_strlen( $content );

				if ( empty( $content ) || strlen( $text_only ) === 0 ) {
					wp_send_json_error( array( 'msg' => esc_html__( 'Cannot rewrite empty content!', 'smodinrewriter' ) ) );
					break;
				}
				if ( $length > self::MAX_LENGTH ) {
					wp_send_json_error( array( 'msg' => sprintf( esc_html__( 'Content (%1$d characters) exceeds %2$d characters. Cannot rewrite!', 'smodinrewriter' ), $length, self::MAX_LENGTH ) ) );
					break;
				}

				$lang = $this->get_languages( filter_var( $_POST['lang'], FILTER_SANITIZE_STRING ) );

				// if the language is not a valid language, abort!
				if ( empty( $lang ) ) {
					wp_send_json_error();
				}

				$strength = filter_var( $_POST['strength'], FILTER_VALIDATE_INT, array( 'options' => array( 'default' => self::DEFAULT_STRENGTH, 'min_range' => 1, 'max_range' => 3 ) ) );

				wp_send_json_success( array( 'count' => $length, 'message' => sprintf( esc_html__( 'Rewrite %1$d characters in %2$s (%3$s) with strength %4$d?', 'smodinrewriter' ), $length, $lang['language'], $lang['nativeName'], intval( $strength ) ) ) );
				break;

			case 'rewrite':
				$content = wp_filter_post_kses( trim( $_POST['content'] ) );

				$lang = $this->get_languages( filter_var( $_POST['lang'], FILTER_SANITIZE_STRING ) );

				// if the language is not a valid language, abort!
				if ( empty( $lang ) ) {
					wp_send_json_error();
				}

				$strength = filter_var( $_POST['strength'], FILTER_VALIDATE_INT, array( 'options' => array( 'default' => self::DEFAULT_STRENGTH, 'min_range' => 1, 'max_range' => 3 ) ) );

				SmodinRewriter_Util::log( sprintf( 'Rewriting %s of length %d', $content, strlen( $content ) ), 'debug' );
				$new = SmodinRewriter_Util::call_api( $content, $lang['symbol'], intval( $strength ) );

				$debug = sprintf(
					'
				<b>%s</b>: %s<br>
				<b>%s</b>: %s<br>
				<b>%s</b>: %s<br>
				<b>%s</b>: %s<br>
				',
					'Text', $content,
					'Results', $new,
					'Language', $lang['symbol'],
					'Strength', $strength
				);

				$mailto = sprintf( '%s?subject=WP Plugin Issue %s&body=Click Ctrl+V/Cmd+V to paste the debug information here<br><br>', SMODINREWRITER_EMAILID, date_i18n( 'Y-m-d' ) );
				wp_send_json_success( array( 'content' => $content, 'rewritten' => $new, 'mailto' => $mailto, 'debug' => $debug ) );
				break;

			case 'publish':
				$strength = filter_var( $_POST['strength'], FILTER_VALIDATE_INT, array( 'options' => array( 'default' => 3, 'min_range' => 1, 'max_range' => 3 ) ) );
				$lang = $this->get_languages( filter_var( $_POST['lang'], FILTER_SANITIZE_STRING ) );

				// if the language is not a valid language, abort!
				if ( empty( $lang ) ) {
					return;
				}

				$id = filter_var( $_POST['id'], FILTER_VALIDATE_INT );

				// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				wp_update_post( array( 'ID' => $id, 'post_content' => wp_filter_post_kses( $_POST['content'] ), 'post_status' => sanitize_text_field( $_POST['draft'] ) == 'true' ? 'draft' : 'publish' ) );
				update_post_meta( $id, 'smodinrewriter-lang', $lang['symbol'] );
				update_post_meta( $id, 'smodinrewriter-strength', $strength );
				break;
		}
	}

	/**
	 * Sets up the admin menu.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function setup_menu() {
		$svg_base64_icon = '';

		add_menu_page( SMODINREWRITER_NAME, SMODINREWRITER_NAME, 'edit_posts', SMODINREWRITER_SLUG, array( $this, 'render_settings' ), $svg_base64_icon, 80 );
	}

	/**
	 * Renders the settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	function render_settings() {
		if ( $_POST && isset( $_POST['sr-settings-button'] ) ) {
			$this->save_settings();
		}

		$wp_scripts = wp_scripts();

		wp_enqueue_script( 'smodinrewriter-settings', SMODINREWRITER_ABSURL . '/assets/js/settings.js', array( 'jquery', 'jquery-ui-tabs' ), SMODINREWRITER_VERSION, false );
		wp_localize_script( 'smodinrewriter-settings', 'config', array() );
		wp_enqueue_style( 'smodinrewriter-admin', SMODINREWRITER_ABSURL . '/assets/css/admin.css', array(), SMODINREWRITER_VERSION, false );
		wp_enqueue_style( 'smodinrewriter-jquery-ui', SMODINREWRITER_ABSURL . '/assets/css/lib/jquery-ui/jquery-ui.min.css', array( 'smodinrewriter-admin' ), SMODINREWRITER_VERSION );

		if ( ! extension_loaded( 'mbstring' ) ) {
			$this->error = sprintf( __( 'The extension %s is not installed or enabled. Please make sure it is installed or rewriting will not work.', 'smodinrewriter' ), '<a href="https://www.php.net/manual/en/mbstring.installation.php" target="_new">mbstring</a>' );
			return;
		}

		$languages = $this->get_languages();
		$strength = $this->get_strengths();

		include_once SMODINREWRITER_ABSPATH . '/views/settings.php';
	}

	/**
	 * Saves the settings from the settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function save_settings() {
		if ( ! wp_verify_nonce( $_POST['nonce'], SMODINREWRITER_SLUG ) ) {
			wp_die();
		}

		$settings = get_option( 'smodinrewriter-settings' );
		if ( ! $settings ) {
			$settings = array();
		}

		$params = array( 'apikey', 'lang', 'strength' );
		foreach ( $params as $key ) {
			$config[ $key ] = sanitize_text_field( $_POST[ $key ] );
		}

		delete_option( 'smodinrewriter-settings' );

		// let's check if the API key works
		$result = SmodinRewriter_Util::call_api( 's', 'en', 3, $config['apikey'] );
		if ( is_wp_error( $result ) ) {
			$this->error = sprintf( __( 'Error from API: %s', 'smodinrewriter' ), $result->get_error_message() );
			return;
		}

		$merged = stripslashes_deep( array_merge( $settings, $config ) );
		add_option( 'smodinrewriter-settings', $merged );

		$this->notice = __( 'Settings saved', 'smodinrewriter' );
	}

	/**
	 * Redirect to the settings page on activation.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function admin_init() {
		if ( get_option( 'smodinrewriter-temp-activated' ) ) {
			delete_option( 'smodinrewriter-temp-activated' );
			if ( ! headers_sent() ) {
				wp_redirect( admin_url( 'admin.php?page=' . SMODINREWRITER_SLUG ) );
				exit();
			}
		}
	}
}
