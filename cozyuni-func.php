<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!function_exists('cozyuni_clean')) {
		function cozyuni_clean($string) {
			$string = str_replace(' ', '-', $string);

			return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
		}
	}

	if (!function_exists('cozyuni_handleRemoteTodo')) {
		function cozyuni_handleRemoteTodo($todo) {
			if ($todo == "register") {
				header("Location: /wp-login.php?action=register");
				exit;
			} else if ($todo == "logout") {
				header("Location: /wp-login.php?action=logout");
				exit;
			} else {
				write_log("unknown remotetodo: " . $todo);
			}
		}
	}

	if (!function_exists('cozyuni_isSubscriber')) {
		function cozyuni_isSubscriber($user) {
			return user_can($user, "subscriber");
		}
	}

	if (!function_exists('cozyuni_getBaseUrl')) {
		function cozyuni_getBaseUrl() {
			return get_option("cozyuni_baseurl");
		}
	}

	if (!function_exists('cozyuni_getApiKey')) {
		function cozyuni_getApiKey() {
			return get_option("cozyuni_apikey");
		}
	}

	if (!function_exists('cozyuni_getWsPass')) {
		function cozyuni_getWsPass() {
			return get_option("cozyuni_wspass");
		}
	}

	if (!function_exists('cozyuni_setAdminMsg')) {
		function cozyuni_setAdminMsg($msg, $isError = false) {
			global $gl_adminMsg, $gl_adminMsgIsError;
			$gl_adminMsg = $msg;
			$gl_adminMsgIsError = $isError;
		}
	}

	if (!function_exists('cozyuni_printAdminMsg')) {
		function cozyuni_printAdminMsg() {
			global $gl_adminMsg, $gl_adminMsgIsError;
			if (!empty($gl_adminMsg)) {
				?>
				<div class="<?= $gl_adminMsgIsError ? "cozyuni_adminmsg_error" : "cozyuni_adminmsg_ok" ?>"><?= $gl_adminMsg ?></div>
				<?php
			}
		}
	}

	if (!function_exists('cozyuni_getErrorListener')) {
		function cozyuni_getErrorListener() {
			return function ($resp) {
				cozyuni_setAdminMsg($resp, true);
			};
		}
	}

	if (!function_exists('cozyuni_printStep')) {
		function cozyuni_printStep($title, $done, $hint = "") {
			?>
			<li class="<?= ($done ? "cozyuni_done" : "cozyuni_notdone"); ?>"><?= $title ?> <?= ($hint != "") ? ": " . $hint : "" ?></li>
			<?php
		}
	}

	if (!function_exists('cozyuni_printAdminCss')) {
		function cozyuni_printAdminCss() {
			?>
			<style>
				.compact-form-table th {
					text-align: right !important;
					padding-bottom: 0px;
					padding-top: 0px;
				}

				.compact-form-table td {
					padding-bottom: 0px;
					padding-top: 0px;
				}

				.form-table .cozyuni_done, .form-table .cozyuni_notdone{
					padding: 0.5rem;
				}

				.cozyuni_adminmsg_ok, .cozyuni_adminmsg_error {
					font-weight: bold;
					padding: 0.5rem;
				}

				.cozyuni_adminmsg_ok {
					background-color: lightblue;
				}

				.cozyuni_adminmsg_error {
					background-color: lightyellow;
				}

				.cozyuni_done, .cozyuni_notdone {
					padding: 0.5rem;
				}

				.cozyuni_done {
					background-color: #d1edd6;

				}

				.cozyuni_notdone {
					background-color: lightyellow;
					font-weight: bold;
				}

				#cozyuni_brand {

					background-color: white;
					padding: 5px;
					margin-top: 2em;

				}
				#main-table td{
					vertical-align: top;
				}


			</style>
			<?php
		}
	}

	if (!function_exists('cozyuni_remote_post')) {
		function cozyuni_remote_post($path, $bodyObj, $cozyuni_apiKey = "") {
			$apiKey = $cozyuni_apiKey != "" ? $cozyuni_apiKey : cozyuni_getApiKey();

			$response = wp_remote_post(COZYUNI_RESTURL . $path, array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8', "apiKey" => $apiKey),
				'body' => json_encode($bodyObj),
				'method' => 'POST',
				'data_format' => 'body'
			));

			if (is_wp_error($response)) {
				return $response->get_error_message();
			} else if (is_array($response)) {
				$respCode = wp_remote_retrieve_response_code($response);
				if ($respCode != 200) {
					return "Wrong response code: " . $respCode . ", with body: " . wp_remote_retrieve_body($response);
				} else {
					return json_decode(wp_remote_retrieve_body($response), true);
				}
			}
		}
	}

	if (!function_exists('cozyuni_remote_get')) {
		function cozyuni_remote_get($path, $cozyuni_apiKey = "") {
			$apiKey = $cozyuni_apiKey != "" ? $cozyuni_apiKey : cozyuni_getApiKey();

			$response = wp_remote_get(COZYUNI_RESTURL . $path, array(
				'headers' => array('Content-Type' => 'application/json; charset=utf-8', "apiKey" => $apiKey)
			));

			if (is_wp_error($response)) {
				return $response->get_error_message();
			} else if (is_array($response)) {
				$respCode = wp_remote_retrieve_response_code($response);
				if ($respCode != 200) {
					return "Wrong response code: " . $respCode . ", with body: " . wp_remote_retrieve_body($response);
				} else {
					return json_decode(wp_remote_retrieve_body($response), true);
				}
			}
		}
	}

	if (!function_exists('cozyuni_addslashes_deep')) {
		function cozyuni_addslashes_deep($value) {
			if (is_array($value)) {
				$value = array_map('cozyuni_addslashes_deep', $value);
			} elseif (is_object($value)) {
				$vars = get_object_vars($value);
				foreach ($vars as $key => $data) {
					$value->{$key} = cozyuni_addslashes_deep($data);
				}
			} elseif (is_string($value)) {
				$value = addslashes($value);
			}

			return $value;
		}
	}

	if (!function_exists('cozyuni_generic_slashes_wrap')) {
		function cozyuni_generic_slashes_wrap(&$arr, $key, $value = null) {
			if (func_num_args() === 2) return stripslashes_deep($arr[$key]);
			else $arr[$key] = cozyuni_addslashes_deep($value);
		}
	}

	if (!function_exists('_cozyuni_get')) {
		function _cozyuni_get($key, $value = null) {
			if (func_num_args() === 1) return cozyuni_generic_slashes_wrap($_GET, $key); else cozyuni_generic_slashes_wrap($_GET, $key, $value);
		}
	}

	if (!function_exists('_cozyuni_post')) {
		function _cozyuni_post($key, $value = null) {
			if (func_num_args() === 1) return cozyuni_generic_slashes_wrap($_POST, $key); else cozyuni_generic_slashes_wrap($_POST, $key, $value);
		}
	}

	if (!function_exists('_cozyuni_cookie')) {
		function _cozyuni_cookie($key, $value = null) {
			if (func_num_args() === 1) return cozyuni_generic_slashes_wrap($_COOKIE, $key); else cozyuni_generic_slashes_wrap($_COOKIE, $key, $value);
		}
	}

	if (!function_exists('_cozyuni_server')) {
		function _cozyuni_server($key, $value = null) {
			if (func_num_args() === 1) return cozyuni_generic_slashes_wrap($_SERVER, $key); else cozyuni_generic_slashes_wrap($_SERVER, $key, $value);
		}
	}

	if (!function_exists('_cozyuni_request')) {
		function _cozyuni_request($key, $value = null) {
			if (func_num_args() === 1) return cozyuni_generic_slashes_wrap($_REQUEST, $key); else cozyuni_generic_slashes_wrap($_REQUEST, $key, $value);
		}
	}


