<?php

class Atmention_basic {

	/**
	 * Updates default plugin settings option on activation
	 */
	public static function on_activation() {
		$mail_text  = 'Hello, ';
		$mail_text .= 'Someone mentioned you in a comment ';
		$mail_text .= 'Post title: %title% ';
		$mail_text .= 'Post link: %post% ';
		$mail_text .= 'Comment link: %link% ';

		$default_options = array (
			'color_name' => 'blue',
			'mail_text'  => $mail_text,
		);

		update_option( 'atmention_options', $default_options );
	}

	/**
	 * Deletes the atmention options on uninstall
	 */
	public static function on_uninstall() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit();
		}

		delete_option( 'atmention_options' );
	}
}