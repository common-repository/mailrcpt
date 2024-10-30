<?php

	/*
		Plugin Name: MailRCPT
		Plugin URI: http://www.mailrcpt.com/
		Description: Integerate MailRCPT (newsletter slash mailing list hosting) in Wordpress
		Version: 0.18
		Author: Twan
	 */

	if (!defined('COZYUNI_DIR')) {
		$cozyUniDir="/usr/local/apache/htdocs/cozyuniwp/";
		if(!file_exists($cozyUniDir)){
			$cozyUniDir=plugin_dir_path(__FILE__) ;
		}
		define('COZYUNI_DIR', $cozyUniDir );
	}

	//captcha
	require_once COZYUNI_DIR.'cozyuni-captcha-func.php';
	add_action('after_setup_theme', 'cozyuni_include_require_files');

	//const
	if (!defined('COZYUNI_RESTURL')) {
		define('COZYUNI_RESTURL',  "http://rest.mailrcpt.com/dash/rest");
	}


	//base cozyuni functionality
	require_once COZYUNI_DIR ."cozyuni-data.php";
	require_once COZYUNI_DIR ."cozyuni-func.php";
	require_once COZYUNI_DIR ."cozyuni-misc.php";
	require_once COZYUNI_DIR ."cozyuni-sync.php";
	require_once COZYUNI_DIR ."cozyuni-cache.php";

	//widgets
	include_once "mailrcpt-mailings-widget.php";
	add_action('widgets_init', 'mailrcpt_widgets_init');
	function mailrcpt_widgets_init() {
		register_widget('MailRCPT_Mailings_Widget');
	}

	//mailrcpt func
	require_once(plugin_dir_path(__FILE__) . "func.php");
	require_once(plugin_dir_path(__FILE__) . "func-page.php");
	require_once(plugin_dir_path(__FILE__) . "admin.php");
	require_once(plugin_dir_path(__FILE__) . "ws.php");
	require_once(plugin_dir_path(__FILE__) . "pages-post.php");
	require_once(plugin_dir_path(__FILE__) . "pages-func.php");
	require_once(plugin_dir_path(__FILE__) . "pages.php");
	require_once(plugin_dir_path(__FILE__) . "fix.php");

	//plugin links
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mailrcpt_plugin_action_links');
	function mailrcpt_plugin_action_links($links) {
		write_log("mailrcpt_plugin_action_links");
		$links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=mailrcpt-options')) . '">Settings</a>';
		$links[] = '<a href="http://www.mailrcpt.com" target="_blank">MailRCPT home</a>';
		return $links;
	}








