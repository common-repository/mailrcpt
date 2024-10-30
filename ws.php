<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_handleWebService() {
		write_log("mailrcpt_handleWebService");

		if (preg_match("/^\/mailrcpt\/ws\/(.*?)$/", $_SERVER['REQUEST_URI'], $matches)) {
			$action = $matches[1];
			if (!mailrcp_isValidWsRequest()) {
				mailrcpt_sendWsErrorAndExit("Invalid auth");
			}
			if ($action == "createuser") {
				mailrcpt_createUser();
			}
		}
	}

	function mailrcp_isValidWsRequest() {
		//apiKey, intModeRemotePass
		write_log(var_export($_SERVER, true));
		$apiKeyInHeader = $_SERVER["HTTP_APIKEY"];
		$intModeRemoteWsPassInHeader = $_SERVER["HTTP_INTMODEREMOTEWSPASS"];
		if ($apiKeyInHeader != "" && $intModeRemoteWsPassInHeader != "") {
			if ($apiKeyInHeader == cozyuni_getApiKey() && $intModeRemoteWsPassInHeader == cozyuni_getWsPass()) {
				return true;
			}
		}
		return false;

	}

	function mailrcpt_createUser() {
		write_log("mailrcpt_createUser");

		$incomingJson=file_get_contents('php://input');
		write_log($incomingJson);
		$data = json_decode($incomingJson, true);
		$userRest = new cozyuni_UserRest(0, "", "", "", "", "");
		$userRest->set($data);
		$userId = wp_create_user($userRest->getUsername(), $userRest->getPass(), $userRest->getEmail());
		if (is_wp_error($userId)) {
			mailrcpt_sendWsErrorAndExit($userId->get_error_message());
		}
		$json=json_encode(new cozyuni_UserRest($userId, $userRest->getUsername(), $userRest->getEmail(), $userRest->getPass(), $userRest->getTitle(), ""));
		write_log($json);
		print $json;
	}

	function mailrcpt_sendWsErrorAndExit($msg) {
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: application/json');
		print json_encode( array("errorMsg" => $msg));
		exit;
	}