<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	function mailrcpt_admin_generateThemeInfo() {
		write_log("mailrcpt_admin_generateThemeInfo");

		//create page
		$my_post = array(
			'post_title'    => wp_strip_all_tags( 'my_custom_page_title' ),
			'post_content'  => 'my_custom_page_content',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type'     => 'page',
		);
		$postId=wp_insert_post( $my_post );

		//get page url
		$url=get_permalink($postId);

		//get page contents
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:53.0) Gecko/20100101 Firefox/53.0',
			CURLOPT_HTTPHEADER => array( "Content-Type: text/html; charset=utf-8")
		));
		$resp = curl_exec($curl);
		curl_close($curl);


		//remove page
		wp_delete_post($postId, false);

		//header
		ob_start();
		get_header();
		$header=ob_get_contents();
		ob_end_clean();

		//footer or sidebar
		ob_start();
		get_footer();
		$footer=ob_get_contents();
		ob_end_clean();

		//get last line of header
		$lines=explode("\n", trim($header));
		$lastLine=trim($lines[count($lines)-1]);
		$lastLine=trim(substr($lastLine, -20));

		//get first line of footer
		$lines=explode("\n", trim($footer));
		$firstLine=trim($lines[0]);
		$firstLine=trim(substr($firstLine, 0, 20));

		//regex
		$regex="/".preg_quote($lastLine, "/")."(.*?)my_custom_page_title(.*?)my_custom_page_content(.*?)".preg_quote($firstLine, "/")."/s";
		write_log($regex);
		if(preg_match($regex, $resp, $matches)) {
			update_option("mailrcpt_page_start", trim($matches[1]));
			update_option("mailrcpt_page_middle", trim($matches[2]));
			update_option("mailrcpt_page_end",trim($matches[3]));

			update_option("mailrcpt_genThemeDone", true);

			cozyuni_setAdminMsg("Saved");
		}
		else{
			cozyuni_setAdminMsg("Could not generate theme info", true);
		}
	}