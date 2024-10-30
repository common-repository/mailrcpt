<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!function_exists('cozyuni_clearCached')) {
		function cozyuni_clearCached($key) {
			delete_option("cozyuni_" . $key . "_data");
			delete_option("cozyuni_" . $key . "_lastupdate");
		}
	}

	if (!function_exists('cozyuni_getCached')) {
		function cozyuni_getCached($key) {
			$cozyuni_lastUpdate = get_option("cozyuni_" . $key . "_lastupdate", 0);
			$data = false;
			if ((time() - $cozyuni_lastUpdate) < (60 * 5)) {
				//get widget content from cache
				$data = get_option("cozyuni_" . $key . "_data");
			}
			return $data;
		}
	}

	if (!function_exists('cozyuni_setCached')) {
		function cozyuni_setCached($key, $data) {
			update_option("cozyuni_" . $key . "_data", $data);
			update_option("cozyuni_" . $key . "_lastupdate", time());
		}
	}