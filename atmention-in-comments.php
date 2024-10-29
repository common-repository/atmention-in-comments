<?php

/**
 * @package    AtMention In Comments
 * @author     Shycoder
 *
 * Plugin Name: AtMention in Comments
 * Plugin URI: https://wordpress.org/plugins/atmention-in-comments
 * Description: Mention both registered and non registered @comment_authors in post comments
 * Version: 2.0.1
 * Author: Shycoder
 * Author URI: https://shycoder.com
 */

defined('ABSPATH') or die('No script kiddies please!');

require_once(plugin_dir_path(__FILE__) . 'classes/class.comments.php');
require_once(plugin_dir_path(__FILE__) . 'classes/class.settings.php');
require_once(plugin_dir_path(__FILE__) . 'classes/class.atmention.php');

if (get_option('atmention_options') === false) {
	register_activation_hook(__FILE__, array('AtMention', 'on_activation'));
}

// initialize needed classes
Atmention_Settings::initialize();
Atmention_Comments::initialize();
