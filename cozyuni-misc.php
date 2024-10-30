<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!function_exists('cozyuni_startsWith')) {
		function cozyuni_startsWith($needle, $haystack) {
			$length = strlen($needle);
			return (substr($haystack, 0, $length) === $needle);
		}
	}

	if (!function_exists('cozyuni_contains')) {
		function cozyuni_contains($needle, $haystack) {
			return strpos($haystack, $needle) !== false;
		}
	}

	if (!function_exists('write_log')) {
		function write_log($log) {
			if (true === WP_DEBUG) {
				if (is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}

	}