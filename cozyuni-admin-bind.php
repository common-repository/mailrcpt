<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */


	if (!function_exists('cozyuni_admin_bind')) {
		function cozyuni_admin_bind($ok, $error, $cozyuni_apiKey, $cozyuni_email, $cozyuni_pass, $cozyuni_wspass) {
			write_log("cozyuni_admin_bind $cozyuni_apiKey");

			$wpUrl = get_site_url();

			$bindReq = new cozyuni_IntModeBindReq($cozyuni_email, $cozyuni_pass, $wpUrl, $cozyuni_wspass);

			$bindResp = cozyuni_remote_post("/bind/bind", $bindReq, $cozyuni_apiKey);
			if (is_array($bindResp)) {
				write_log($bindResp);

				update_option("cozyuni_apikey", $cozyuni_apiKey);
				update_option("cozyuni_baseurl", $bindResp["baseUrl"]);
				update_option("cozyuni_wspass", $cozyuni_wspass);
				update_option("cozyuni_sitekey", $bindResp["intModeCaptchaSite"]);
				update_option("cozyuni_seckey", $bindResp["intModeCaptchaSecret"]);
				$ok();
			} else {
				//cozyuni_setAdminMsg($bindResp);
				$error($bindResp);
			}
		}
	}

	if (!function_exists('cozyuni_admin_unbind')) {
		function cozyuni_admin_unbind($ok, $error, $removeUsers) {
			write_log("cozyuni_admin_unbind");

			$unbindReq = new cozyuni_IntModeUnbindReq($removeUsers ? "true" : "false");
			$unbindResp = cozyuni_remote_post("/bind/unbind", $unbindReq);
			if (is_array($unbindResp)) {
				//
				delete_option("cozyuni_apikey");
				delete_option("cozyuni_baseurl");
				delete_option("cozyuni_wspass");
				delete_option("cozyuni_sitekey");
				delete_option("cozyuni_seckey");
				delete_option("cozyuni_resyncDone");

				//return true;
				$ok();
			} else {
				//cozyuni_setAdminMsg($unbindResp);
				$error($unbindResp);
			}
		}
	}