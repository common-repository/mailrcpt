<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */
	function mailrcpt_admin_save($mailrcpt_templatetype, $cozyuni_sitekey, $cozyuni_seckey) {
		write_log("mailrcpt_admin_save> mailrcpt_templatetype: $mailrcpt_templatetype");

		update_option("mailrcpt_templatetype", $mailrcpt_templatetype);

		update_option("cozyuni_sitekey", $cozyuni_sitekey);
		update_option("cozyuni_seckey", $cozyuni_seckey);
		cozyuni_setAdminMsg("Saved");
	}

	function mailrcpt_admin_generated_save($mailrcpt_page_start, $mailrcpt_page_middle, $mailrcpt_page_end) {
		write_log("mailrcpt_admin_generated_save");


		update_option("mailrcpt_page_start", $mailrcpt_page_start);
		update_option("mailrcpt_page_middle", $mailrcpt_page_middle);
		update_option("mailrcpt_page_end", $mailrcpt_page_end);

		cozyuni_setAdminMsg("Saved");
	}