<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	add_filter( 'body_class', function($classes){
		if(mailrcpt_isActive()){
			$foundWithKey=-1;
			foreach($classes as $key => $value){
				if($value=="error404"){
					$foundWithKey=$key;
					break;
				}
			}
			if($foundWithKey!=-1){
				$classes[$foundWithKey]="page";
			}
		}

		return $classes;
	}, 10, 4);

	add_filter('status_header', function ($status_header, $code, $description, $protocol) {
		if(mailrcpt_isActive()){
			return "$protocol 200 " . get_status_header_desc(200);
		}
		return $status_header;
	}, 10, 4);

	add_filter("pre_get_document_title", function ($title) {
		if(mailrcpt_isActive()) {
			global $gl_pageTitle;
			if(isset($gl_pageTitle)){
				return $gl_pageTitle;
			}else {
				return "MailRCPT";
			}
		}
		return $title;
	}, 10, 4);