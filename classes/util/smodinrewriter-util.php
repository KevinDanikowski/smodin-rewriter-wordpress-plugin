<?php
/**
 * All utility methods.
 *
 * @since      1.0.0
 */
class SmodinRewriter_Util {

	const API_URL = 'https://rewriter-paraphraser-text-changer-multi-language.p.rapidapi.com/rewrite';

	/**
	 * Logs the messages to debug.log file in a specific format.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function log( $msg, $type = 'error' ) {
		if ( SMODINREWRITER_DEBUG || 'error' === $type ) {
			error_log( sprintf( '%s --- %s: %s', 'SmodinRewriter', strtoupper( $type ), $msg ) );
		}
	}

	/**
	 * Gets a specific option from the serialized options.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function get_option( $key, $default = '' ) {
		$value  = $default;
		$settings = get_option( 'smodinrewriter-settings' );
		if ( ! $settings ) {
			$settings = array();
		}
		if ( array_key_exists( $key, $settings ) ) {
			$value = $settings[ $key ];
		}

		return ! empty( $value ) ? $value : ( ! empty( $default ) ? $default : $value );
	}

	/**
	 * Calls the API.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function call_api( $content, $lang = 'en', $strength = 3, $apiKey = null ) {
		$key = is_null( $apiKey ) ? self::get_option( 'apikey' ) : $apiKey;
		$params  = array( 'text' => $content, 'language' => $lang, 'strength' => $strength );
		$response = wp_remote_post( self::API_URL, array( 'timeout' => 20, 'body' => json_encode( $params ), 'headers' => array( 'Content-Type' => 'application/json', 'x-rapidapi-key' => $key ) ) );
		$body = wp_remote_retrieve_body( $response );

		$result = json_decode( $body );

		self::log( sprintf( 'Calling API with params %s and result %s', print_r( $params, true ), print_r( $result, true ) ), 'debug' );

		if ( is_object( $result ) ) {
			if ( isset( $result->message ) && ! empty( $result->message ) ) {
				return new \WP_Error( 'smodin-error', $result->message );
			} else {
				return $result->rewrite;
			}
		}

		return $result;
	}


}
