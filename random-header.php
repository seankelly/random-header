<?php
/**
 * Plugin Name: Random Header
 * Description: Random header images for every page.
 * Version: 0.1.0
 * Author: Sean Kelly
 * License: GPL2+
 */

defined('ABSPATH') or die("This file must be used with WordPress.");

class RandomHeader {
	public static function setup() {
		add_action('init', array(__CLASS__, 'init'));
	}

	public static function init() {
		if (current_theme_supports('custom-header')) {
			add_action('theme_mod_header_image', array(__CLASS__, 'theme_mod_header_image'));
		}
	}

	public static function theme_mod_header_image($header_url) {
	}
}

RandomHeader::setup();
