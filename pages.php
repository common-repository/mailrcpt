<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	add_action('wp', 'mailrcpt_wp');
	function mailrcpt_wp() {
		if (cozyuni_contains('remotetodo', $_SERVER['REQUEST_URI'])) {
			$remotetodo = $_GET["remotetodo"];
			cozyuni_handleRemoteTodo($remotetodo);
			exit;
		}

		if (!mailrcpt_isActive()) {
			return;
		}
		///mailrcpt/ws/createuser
		if (cozyuni_startsWith("/mailrcpt/ws/", $_SERVER["REQUEST_URI"])) {
			mailrcpt_handleWebService();
			exit;
		}

		//get mailrcpt url
		$baseUrl = cozyuni_getBaseUrl();
		$wpBaseUrl = get_site_url() . "/mailrcpt";
		$realFullUrl = mailrcpt_getRealFullUrl($_SERVER['REQUEST_URI'], $baseUrl);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			//handle form posts
			mailrcpt_handlePost($realFullUrl, $wpBaseUrl);
			exit;
		}

		$templateType = get_option("mailrcpt_templatetype");

		//get page content or json from mailrcpt server
			$wpResp = wp_remote_get($realFullUrl, array(
			"user-agent" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:53.0) Gecko/20100101 Firefox/53.0',
			"headers" => array(
				"intMode" => "1",
				"Content-Type" => ($templateType == "mustache" ? "application/json" : "text/html") . "; charset=utf-8",
				"Cookie" => mailrcpt_getCookieHeader(),
				"ApiKey" => mailrcpt_getApiKeyHeader(),
				"intMode-UserId" => mailrcpt_getUserHeader())
		));
		$headers = mailrcpt_processHeaders($wpResp);
		$resp=wp_remote_retrieve_body($wpResp);

		global $gl_pageTitle, $gl_pageContent;
		$gl_pageTitle = "";
		$gl_pageContent = "";

		if ($templateType == "mustache") {
			require plugin_dir_path(__FILE__) . '/Mustache/Autoloader.php';
			Mustache_Autoloader::register();

			$m = new Mustache_Engine(array(
				'escape' => function ($value) {
					return $value;
				},
				'partials_loader' => new Mustache_Loader_FilesystemLoader(get_template_directory()),
			));
			$model = json_decode($resp, true);
			$fixedModel = mailrcpt_fixModel($model);

			if (!isset($headers["cu-themekey"])) {
				die("No theme information found in response header. Have you enabled 'Output JSON in integration mode' in MailRCPT dashboard?");
			}
			$themeKey = cozyuni_clean($headers["cu-themekey"]);
			$themeFile = get_template_directory() . "/" . $themeKey . ".mustache";
			if (!file_exists($themeFile)) {
				die("Mustache theme not found: " . $themeFile . ".");
			}
			$template = file_get_contents($themeFile);

			$html = $m->render($template, $fixedModel);
			$html = mailrcpt_fixPaths($html, $wpBaseUrl);

			mailrcpt_setGlobalVars($html);

			include_once(get_template_directory() . "/page-mailrcpt.php");
		} else if ($templateType == "page") {
			//fix urls
			if(cozyuni_contains("application/json",$headers["content-type"])){
				$body="Unexpected JSON response received. Try disabling 'Output JSON in integration mode' in MailRCPT dashboard. ";
			}else {
				$body = mailrcpt_fixPaths($resp, $wpBaseUrl);
			}
			mailrcpt_setGlobalVars($body);

			include_once(get_template_directory() . "/page-mailrcpt.php");
		} else {
			$body = mailrcpt_fixPaths($resp, $wpBaseUrl);
			mailrcpt_setGlobalVars($body);

			$pageStart = get_option("mailrcpt_page_start");
			$pageMiddle = get_option("mailrcpt_page_middle");
			$pageEnd = get_option("mailrcpt_page_end");

			get_header();
			print $pageStart;
			mailrcpt_pageTitle();
			print $pageMiddle;
			mailrcpt_pageContent();
			print $pageEnd;
			get_footer();
		}

		exit();
	}

