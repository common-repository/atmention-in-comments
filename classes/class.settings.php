<?php

defined('ABSPATH') or die('No script kiddies please!');

class Atmention_Settings
{
	public static $atmention_options;

	/** Initialize WordPress hooks */
	public static function initialize()
	{
		self::$atmention_options = get_option('atmention_options');

		add_action('admin_menu', array('Atmention_Settings', 'settings'));
		add_action('admin_init', array('Atmention_Settings', 'admin_init'));
	}

	public static function settings()
	{

		add_options_page(
			'AtMention in Comments',
			'AtMention in Comments',
			'manage_options',
			'atmention',
			array(__CLASS__, 'option_page')
		);
	}

	public static function option_page()
	{
?>

		<div class="wrap">
			<h2>AtMention in Comments</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields('atmention_options');

				do_settings_sections('atmention');

				submit_button();
				?>
			</form>
		</div>
	<?php
	}

	public static function admin_init()
	{

		// register atmention_options
		register_setting(
			'atmention_options',
			'atmention_options'

		);

		add_settings_section(
			'atmention_main',
			'',
			'',
			'atmention'
		);

		add_settings_field(
			'atmention_mail_text',
			'Notification Email',
			array(__CLASS__, 'mail_template_field'),
			'atmention',
			'atmention_main'
		);

		add_settings_field(
			'atmention_mention_color',
			'@Mention Text Color',
			array(__CLASS__, 'color_field'),
			'atmention',
			'atmention_main'
		);
	}

	/** Notification email texts sent to mentioned authors */
	public static function mail_template_field()
	{
		$atmention_mail_text = (isset(self::$atmention_options['mail_text'])) ? self::$atmention_options['mail_text'] : '';
	?>

		<textarea name="<?php echo esc_attr('atmention_options[mail_text]'); ?>" cols="<?php echo esc_attr('40'); ?>" rows="<?php echo esc_attr('10') ?>"><?php echo wp_kses_post($atmention_mail_text); ?></textarea>
		<p>
			<small>
				<strong>%post%</strong> Post link &mdash; <strong>%link%</strong> Comment link &mdash;
				<strong>%title%</strong> Post title <br>
				Supports HTML email customizations.
			</small>
		</p>

	<?php
	}

	/** @mention text color in comments */
	public static function color_field()
	{
		$color = (isset(self::$atmention_options['color_name'])) ? self::$atmention_options['color_name'] : '#8B0000';
	?>
		<input type="text" value="<?php echo esc_attr($color); ?>" name="<?php echo esc_attr('atmention_options[color_name]'); ?>" />
		<small><?php echo esc_attr('Example: #000000 or black'); ?></small>
<?php

	}
}
