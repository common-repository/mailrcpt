<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_isActive() {
		return cozyuni_startsWith('/mailrcpt/', $_SERVER['REQUEST_URI']);
	}


