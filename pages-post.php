<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_handlePost($fullUrl, $wpBaseUrl) {
		write_log("mailrcpt_handlePost> fullUrl: $fullUrl");


		$refPath = mailrcpt_getPathFromUrl($_SERVER['HTTP_REFERER']);
		$realHttpRef = mailrcpt_getRealFullUrl($refPath, cozyuni_getBaseUrl());
		$post = file_get_contents('php://input');

		$wpResp = wp_remote_post($fullUrl, array(
			"user-agent" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:53.0) Gecko/20100101 Firefox/53.0',
			"headers" => array(
				"Referer" => $realHttpRef,
				"intMode" => "1",
				"Content-Type" => "application/x-www-form-urlencoded",
				"Cookie" => mailrcpt_getCookieHeader(),
				"ApiKey" => mailrcpt_getApiKeyHeader(),
				"intMode-UserId" => mailrcpt_getUserHeader()),
			'body' => $post,
			'method' => 'POST',
			'data_format' => 'body',
			"redirection" => 0
		));

		mailrcpt_processHeaders($wpResp);


	}