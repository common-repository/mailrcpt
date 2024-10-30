<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */
	function mailrcpt_admin_getMustacheThemes() {
		write_log("mailrcpt_admin_getMustacheThemes");

		$templateDir = get_template_directory();
		if (!is_writable($templateDir)) {
			cozyuni_setAdminMsg("This plugin needs to write mustache templates to the template directory. " .
				"It seems that the following directory is not writable: " . $templateDir);
			return;
		}


		$rawThemeKeys = "all";
		$excludeThemeKeys = "css, footer, homewidgets, insertContentInc, simpleFooter, simpleHeader, tagsInc, widgets";

		$resp = cozyuni_remote_get("/theme/get?rawThemeKeys=" . urlencode($rawThemeKeys) . "&rawExcludeThemeKeys=" . urlencode($excludeThemeKeys), cozyuni_getApiKey());
		//write_log($resp);
		if (is_array($resp)) {
			$templateDir = get_template_directory();
			foreach ($resp as $themeKey => $template) {
				file_put_contents($templateDir . "/" . $themeKey . ".mustache", $template);
			}
			cozyuni_setAdminMsg("Mustache templates retrieved and saved");
		} else {
			cozyuni_setAdminMsg("Could not retrieve mustache templates", true);
		}
	}