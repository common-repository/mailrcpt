<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */


	if (!function_exists('cozyuni_include_require_files')) {
		function cozyuni_include_require_files() {
			require_once 'cozyuni-captcha-class.php';
		}
	}

	if (!function_exists('cozyuni_wp_footer')) {
		add_action('wp_footer', 'cozyuni_wp_footer');
		add_action('login_footer', 'cozyuni_wp_footer');
		function cozyuni_wp_footer() {
			cozyuni_captcha_class::init()->footer_script();
		}
	}

	if (!function_exists('cozyuni_captcha_form_field')) {
		function cozyuni_captcha_form_field(/*$echo = false*/) {
			echo cozyuni_captcha_class::init()->captcha_form_field();
		}
	}

	if (!function_exists('cozyuni_add_shake_error_codes')) {
		add_filter('shake_error_codes', 'cozyuni_add_shake_error_codes');
		function cozyuni_add_shake_error_codes($shake_error_codes) {
			$shake_error_codes[] = 'cozyuni_error';

			return $shake_error_codes;
		}
	}

