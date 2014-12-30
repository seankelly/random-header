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
	public function __construct() {
		add_action('init', array($this, 'init'));
	}

	public function init() {
		if (current_theme_supports('custom-header')) {
			add_action('theme_mod_header_image', array($this, 'theme_mod_header_image'));
		}
	}

	public function theme_mod_header_image($header_url) {
		add_filter('posts_where', array($this, media_where_filter), 10, 2);
		$images = new WP_Query(array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'orderby' => 'rand',
			'posts_per_page' => 10,
			'theme_mod_header_image_title' => 'wgom banner',
		));
		remove_filter('posts_where', array($this, media_where_filter), 10, 2);

		echo "<!-- {$images->request} -->";

		foreach ($images->posts as $image) {
			$image_url = wp_get_attachment_url($image->ID);
			echo "<!--  {$image->post_title} : $image_url -->\n";
		}
	}

	public function media_where_filter($where, &$query) {
		global $wpdb;
		if ($title = $query->get('theme_mod_header_image_title')) {
			$where .= ' AND '
				. $wpdb->posts
				. '.post_title LIKE \''
				. esc_sql($wpdb->esc_like($title))
				. '%\'';
		}
		return $where;
	}
}

new RandomHeader();
