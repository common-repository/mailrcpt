<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_pageContent() {
		global $gl_pageContent;
		print $gl_pageContent;
	}

	function mailrcpt_pageTitle() {
		global $gl_pageTitle;
		print $gl_pageTitle;
	}

	function mailrcpt_getPathFromUrl($url){
		$parts=parse_url($url);
		$location=$parts["path"];
		if(isset($parts["query"])){
			$location.="?".$parts["query"];
		}
		return $location;
	}

	function mailrcpt_getRealRelativeUrl($url){
		preg_match("/^\/mailrcpt\/(.*?)$/", $url, $matches);
		$realRelativeUrl = "/" . $matches[1];
		return $realRelativeUrl;
	}

	function mailrcpt_getRealFullUrl($url, $baseUrl){
		return $baseUrl.mailrcpt_getRealRelativeUrl($url);
	}

	function mailrcpt_fixPaths($resp, $wpBaseUrl) {
		$body = $resp;
		//window.location
		$body = preg_replace("/onclick=\"window.location='\//", "onclick=\"window.location='" . $wpBaseUrl . "/", $body);
		//href="/"
		$body = preg_replace("/href=\"\/\"/", " data-changed-1 HREF=\"" . $wpBaseUrl . "/\"", $body);
		//href="/test.html" but not href="//domain.com"
		$body = preg_replace("/href=\"\/([^\/])(.*)\"/", "data-changed-2 HREF=\"" . $wpBaseUrl . "/$1$2\"", $body);
		//action="/";
		$body = preg_replace("/action=\"\//", "data-changed-3 action=\"" . $wpBaseUrl . "/", $body);
		return $body;
	}

	function mailrcpt_setGlobalVars($html) {
		global $gl_pageTitle, $gl_pageContent;
		if (preg_match("/<h1 id=\"pagetitle\">(.*?)<\/h1>/", $html, $matches)) {
			$gl_pageTitle = $matches[1];
			$html = preg_replace("/<h1 id=\"pagetitle\">(.*?)<\/h1>/", "", $html);
		}
		$gl_pageContent = $html;
		return $html;
	}

	function mailrcpt_fixModel($cuModel) {
		$fixedModel = array();
		if (is_array($cuModel)) {

			foreach ($cuModel as $key => $value) {
				$keys = explode(".", $key);
				$keyCount = count($keys);
				if ($keyCount > 1) {
					//style.is.header.showbreadcrumbs
					$subModel =& $fixedModel;
					$index = 0;
					foreach ($keys as $subKey) {
						if ($index == ($keyCount - 1)) {
							//last one
							$subModel[$subKey] = $value;
							break;
						}
						if (!isset($subModel[$subKey]) || !is_array($subModel[$subKey])) {
							$subModel[$subKey] = array();
						}
						$subModel =& $subModel[$subKey];
						$index++;
					}
				} else {
					$fixedModel[$key] = $value;
				}
			}
		}
		mailrcpt_formatDates($fixedModel);
		return $fixedModel;
	}

	function mailrcpt_formatDates(&$model, $level = 0) {
		if (is_array($model)) {
			foreach ($model as $key => &$val) {
				if (is_array($val)) {
					mailrcpt_formatDates($val, ++$level);
				} else {
					if (is_numeric($val)) {
						//1550756580000
						if (cozyuni_contains("Date", $key)) {
							if (strlen((string)$val) == 13) {
								$model[$key] = date("F j, Y, g:i a", ($val / 1000));
							}
						}
					}
				}
			}
		}


	}