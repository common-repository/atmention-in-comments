<?php

defined('ABSPATH') or die('No script kiddies please!');

class Atmention_Comments {
	public static $atmention_options;

	/**
	 * Load WordPress hooks on initializtion
	 */
	public static function initialize() {
		self::$atmention_options = get_option( 'atmention_options' );

		add_filter( 'comment_text', array ( 'Atmention_Comments', 'modify_comment' ) );
		add_action( 'wp_set_comment_status', array ( 'Atmention_Comments', 'notify_on_approval' ), 10, 2 );
		add_action( 'wp_insert_comment', array ( 'Atmention_Comments', 'notify' ), 10, 2 );
	}

	/**
	 * We work on the comments here to check if anyone is mentioned,
	 * and if anyone is mentioned, style the mention text
	 *
	 * @param mixed $comment The comment
	 * @return mixed $mod_comment The comment modified
	 */
	public static function modify_comment( $comment ) {

		// get the preferred color set by admin and style mentions
		$color_name  = self::$atmention_options[ 'color_name' ];

		$pattern     = "/(^|\s)@(\w+)/";
		$replacement = "<span style='color: $color_name;'>$0</span>";
		$mod_comment = preg_replace( $pattern, $replacement, $comment );

		return $mod_comment;
	}

	/**
	 * Here we send notification email to mentioned authors,
	 * when a comment held for moderation is approved.
	 *
	 * @param int $comment_id The commend id
	 * @param string $status The comment status
	 */
	public static function notify_on_approval( $comment_id, $status ) {
		$comment = get_comment( $comment_id, OBJECT );
		( $comment && $status == 'approve' ? self::send_mail( $comment ) : null );
	}

	/**
	 * We send a notification email to mentioned authors if the
	 * comment was never held for moderation but rather automatically
	 * approved when submitted
	 *
	 * @param int $comment_id The comment id
	 */
	public static function notify( $comment_id, $comment_object ) {
		( wp_get_comment_status( $comment_id ) == 'approved' ? self::send_mail( $comment_object ) : null );
	}

	/**
	 * Generate email address of authors
	 *
	 * @param string $name author name
	 *
	 * @return string email
	 */
	public static function get_author_email( $name ) {
		global $wpdb;

		$name  = sanitize_text_field( $name );
		$query = "SELECT comment_author_email FROM {$wpdb->comments} WHERE comment_author = %s ";

		$prepare_email_address = $wpdb->prepare( $query, $name );
		$email_address         = $wpdb->get_var( $prepare_email_address );

		return $email_address;
	}

	/**
	 * Send notification email to mentioned comment author
	 *
	 * @param mixed $comment The comment
	 *
	 */
	private static function send_mail( $comment ) {
		$the_related_post        = $comment->comment_post_ID;
		$the_related_comment     = $comment->comment_ID;
		$the_related_post_url    = get_permalink( $the_related_post );
		$the_related_comment_url = get_comment_link( $the_related_comment );
		$the_related_post_title  = get_the_title( $the_related_post );

		// get the mentions
		$the_comment = $comment->comment_content;
		$pattern     = "/(^|\s)@(\w+)/";

		if ( preg_match_all( $pattern, $the_comment, $match ) ) {

			// remove all @s from comment author names to effectively
			// generate appropriate email addresses of authors mentioned
			foreach ( $match as $m ) {
				$email_owner_name = preg_replace( '/@/', '', $m );
				$email_owner_name = array_map( 'trim', $email_owner_name );
			}

			/**
			 * For full names, make comment authors replace spaces them  with
			 * two underscores. e.g, John Doe would be mentioned as @John__Doe
			 *
			 * PS: Mentions are case insensitive
			 */
			if ( preg_match_all( '/\w+__\w+/', implode( '', $email_owner_name ) ) ) {
				$email_owner_name = str_ireplace( '__', ' ', $email_owner_name );
			}

			// we generate all the mentioned comment author(s) email addresses
			$author_emails = array_map( 'self::get_author_email', $email_owner_name );

			// ensure at least one valid comment author is mentioned before sending email
			// TODO: If I get the chance to update this plugin, make sure subject isn't hardcoded
			if ( ! is_null( $author_emails ) ) {
				$subj = '[' . get_bloginfo( 'name' ) . '] Someone mentioned you in a comment';
				$body = self::$atmention_options[ 'mail_text' ];

				/**
				 * After getting the notification email from atmention options,
				 * we replace all included %tags% with appropriate values.
				 */
				
				$body = str_ireplace( '%post%', $the_related_post_url, $body );
				$body = str_ireplace( '%link%', $the_related_comment_url, $body );
				$body = str_ireplace( '%title%', $the_related_post_title, $body );

				wp_mail( $author_emails, $subj, $body, "Content-Type: text/html;charset=UTF8\r\n" );

			}
		}
	}

}
