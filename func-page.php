<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_processHeaders($wpResp){//$curl, $header){
		//write_log("mailrcpt_processHeaders");
		$http_status=wp_remote_retrieve_response_code($wpResp);
		$headers=wp_remote_retrieve_headers($wpResp);

		mailrcpt_processCookies($headers);

		//write_log(var_export($headers, true));

		if ($http_status == 302) {
			if (isset($headers["Location"])) {
				$location=$headers["Location"];
				if(cozyuni_startsWith("http", $location)){
					$parts=parse_url($location);
					$location=$parts["path"];
					if($parts["query"]!=""){
						$location.="?".$parts["query"];
					}
				}
				header("Location: " . get_site_url()."/mailrcpt" . $location);
				exit;
			}
		}
		return $headers;
	}

	function mailrcpt_getHeaders($headerContent) {
		$headers = array();

		// Split the string on every "double" new line.
		$arrRequests = explode("\r\n\r\n", $headerContent);

		// Loop of response headers. The "count() -1" is to
		//avoid an empty row for the extra line break before the body of the response.
		for ($index = 0; $index < count($arrRequests) -1; $index++) {

			foreach (explode("\r\n", $arrRequests[$index]) as $i => $line)
			{
				if ($i === 0)
					$headers[$index]['http_code'] = $line;
				else
				{
					list ($key, $value) = explode(': ', $line);
					$headers[$index][$key] = $value;
				}
			}
		}

		return $headers;
	}

	function mailrcpt_getApiKeyHeader() {
		return "" . cozyuni_getApiKey();
	}

	function mailrcpt_getUserHeader(){
		return "".get_current_user_id();
	}

	function mailrcpt_getCookieHeader() {
		if (isset($_COOKIE["JSESSIONID"])) {
			write_log("adding to curl: " . $_COOKIE["JSESSIONID"]);
			return "JSESSIONID=" . $_COOKIE["JSESSIONID"];
		}
		return "";
	}

	function mailrcpt_processCookies($headers) {
		//if (isset($headers["Set-Cookie"])) {
		if (isset($headers["set-cookie"])) {
			if (preg_match("/JSESSIONID=(.*?);/", $headers["set-cookie"], $matches)) {
				//update_option(""$matches[1];
				setcookie("JSESSIONID", $matches[1], 0, "/");
				write_log("sending cookie to user: " . $matches[1]);
			}
		}

		/*if (isset($headers[0]["Set-Cookie"])) {
			if (preg_match("/JSESSIONID=(.*?);/", $headers[0]["Set-Cookie"], $matches)) {
				//update_option(""$matches[1];
				setcookie("JSESSIONID", $matches[1], 0, "/");
				write_log("sending cookie to user: " . $matches[1]);
			}
		}*/
	}