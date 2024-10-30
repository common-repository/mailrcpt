<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!class_exists('cozyuni_captcha_class')) {
		class cozyuni_captcha_class {
			private static $instance;

			public static function init() {
				if (!self::$instance instanceof self) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			function actions_filters() {
				add_action('register_form', array($this, 'form_field'), 99);
				add_filter('registration_errors', array($this, 'registration_verify'), 10, 3);
			}

			function form_field() {
				cozyuni_captcha_form_field(true);
			}

			function registration_verify($errors, $sanitized_user_login, $user_email) {
				if (!$this->verify_captcha()) {
					$errors->add('cozyuni_error', $this->add_error_to_mgs());
				}
				return $errors;
			}

			function verify_captcha($response = false) {
				$cozyuni_sitekey = trim(get_option('cozyuni_sitekey'));
				$cozyuni_seckey = trim(get_option('cozyuni_seckey'));
				$remoteip = $_SERVER['REMOTE_ADDR'];

				if ($cozyuni_seckey=="" || $cozyuni_sitekey=="") {
					return false;
				}

				if (false === $response) {
					$response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
				}

				if (!$response || !$remoteip) {
					return false;
				}


				$request = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify', array(
						'timeout' => 10,
						'body' => array(
							'secret' => $cozyuni_seckey,
							'response' => $response,
							'remoteip' => $remoteip,
						),
					)
				);

				if (is_wp_error($request)) {
					return false;
				}

				$request_body = wp_remote_retrieve_body($request);
				if (!$request_body) {
					return false;
				}

				$result = json_decode($request_body, true);
				if (isset($result['success']) && true == $result['success']) {
					return true;
				}

				return false;
			}

			function add_error_to_mgs() {
				return '<strong>ERROR</strong>: Please solve Captcha correctly';
			}

			function captcha_form_field() {
				$field ='<div class="cozyuni_captcha_field"><div id="cozyuni_captcha_field"></div></div>';
				return $field;
			}

			function footer_script() {
				static $included = false;
				if ($included) {
					return;
				}
				$included = true;

				$site_key = trim(get_option('cozyuni_sitekey'));
				if($site_key==""){
					$site_key="invalid";
				}
				$theme = "light";
				$size = "normal";
				?>
				<script type="text/javascript">
            var cozyuni_onloadCallback = function () {
                var cozyuni_obj = {
                    'sitekey': '<?php echo esc_js($site_key); ?>',
                    'size': '<?php echo esc_js($size); ?>',
                };

                cozyuni_obj.theme = '<?php echo esc_js($theme); ?>';

                var cozyuni_captcha = grecaptcha.render('cozyuni_captcha_field', cozyuni_obj);
                if (typeof wc_checkout_params !== 'undefined') {
                    jQuery(document.body).on('checkout_error', function () {
                        grecaptcha.reset(cozyuni_captcha);
                    });
                }

            };
				</script>
				<script src="https://www.google.com/recaptcha/api.js?onload=cozyuni_onloadCallback&render=explicit" async defer></script>
				<?php
			}



		}
	}

	add_action('init', array(cozyuni_captcha_class::init(), 'actions_filters'), -9);

