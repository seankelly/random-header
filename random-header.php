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
	private $menu_slug = 'random-header';
	private $options = array(
		'random-header-media-prefix',
	);

	private $header_url;

	public function __construct() {
		$this->header_url = null;
		add_action('init', array($this, 'init'));
	}

	public function init() {
		if (current_theme_supports('custom-header')) {
			add_action('theme_mod_header_image', array($this, 'theme_mod_header_image'));
			add_action('admin_init', array($this, 'add_settings'));
			add_action('admin_menu', array($this, 'admin_menu'));
		}
	}

	public function theme_mod_header_image($header_url) {
		if (!is_null($this->header_url)) {
			return $this->header_url;
		}

		add_filter('posts_where', array($this, media_where_filter), 10, 2);
		$images = new WP_Query(array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
			'orderby' => 'rand',
			'posts_per_page' => 1,
			'theme_mod_header_image_title' => get_option('random-header-media-prefix'),
		));
		remove_filter('posts_where', array($this, media_where_filter), 10, 2);

		if ($images->have_posts()) {
			$this->header_url = wp_get_attachment_url($images->posts[0]->ID);
			return $this->header_url;
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

	/**
	 * Add a submenu under the Settings menu.
	 */
	public function admin_menu() {
		add_options_page(
			'Random Header',
			'Random Header',
			'manage_options',
			$this->menu_slug,
			array($this, 'cb_admin_menu')
		);
	}

	public function add_settings() {
		// Do not want a title or a callback for the section. There is
		// only a single setting so adding extra output from the
		// section just adds clutter.
		add_settings_section(
			'default',
			'',
			'',
			$this->menu_slug
		);

		foreach ($this->options as $opt) {
			add_settings_field(
				$opt,
				'Media prefix',
				array($this, 'cb_settings_field'),
				$this->menu_slug,
				'default',
				array(
					$opt,
				)
			);
			register_setting($this->menu_slug, $opt);
		}
	}

	public function cb_admin_menu() {
	?>
	<div class="wrap">
	<h2>Random Header</h2>
	<form method="post" action="options.php">
	<?php
		settings_fields($this->menu_slug);
		do_settings_sections($this->menu_slug);
		submit_button();
	?>
	</form>
	</div>
	<?php
	}

	public function cb_settings_field($args) {
		$option_name = $args[0];
		$option_value = get_option($option_name);
		echo "<input id='$option_name' name='$option_name' value='" . esc_attr($option_value) . "' type='text'></input>";
	}
}

new RandomHeader();
