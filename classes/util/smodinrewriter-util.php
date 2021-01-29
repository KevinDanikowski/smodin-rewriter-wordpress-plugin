<?php
/**
 * All utility methods.
 *
 * @since      1.0.0
 */
class SmodinRewriter_Util {

	const API_URL = 'https://rewriter-paraphraser-text-changer-multi-language.p.rapidapi.com/rewrite';

	public static function log( $msg, $type = 'error' ) {
		error_log( sprintf( '%s --- %s: %s', 'SmodinRewriter', strtoupper( $type ), $msg ) );
	}

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

	public static function call_api( $content, $lang = 'en', $strength = 3 ) {
		$params  = array( 'text' => $content, 'language' => $lang, 'strength' => $strength );
		$response = wp_remote_post( self::API_URL, array( 'body' => json_encode( $params ), 'headers' => array( 'Content-Type' => 'application/json', 'x-rapidapi-key' => self::get_option( 'apikey' ) ) ) );
		$body = wp_remote_retrieve_body( $response );

		$result = json_decode( $body );

		self::log( sprintf( 'Calling %s with result %s', $url, print_r( $result, true ) ), 'debug' );

		// the API returns empty even on an error (e.g. invalid API key).
		if ( is_object( $result ) && ! is_wp_error( $result ) ) {
			return $result->rewrite;
		} elseif ( is_wp_error( $result ) ) {
			return new \WP_Error( 'smodin-error', $result->get_error_message() );
		}

		return $result;
	}


}
