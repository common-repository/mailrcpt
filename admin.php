<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */
	if (is_admin()) {
		add_action('admin_init', 'mailrcpt_admin_init');
		add_action('admin_menu', 'mailrcpt_admin_menu');
	}

	function mailrcpt_admin_menu() {
		add_options_page('MailRCPT Options', 'MailRCPT', 'manage_options', 'mailrcpt-options', 'mailrcpt_plugin_options');
	}

	function mailrcpt_admin_init() {
		register_setting('mailrcpt_options_group', 'mailrcpt_usethemefile');
		register_setting('mailrcpt_options_group', 'mailrcpt_page_start');
		register_setting('mailrcpt_options_group', 'mailrcpt_page_middle');
		register_setting('mailrcpt_options_group', 'mailrcpt_page_end');
		add_filter('admin_footer_text', '__return_false', 11);
		add_filter('update_footer', '__return_false', 11);
	}


	function mailrcpt_plugin_options() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		include_once "admin-misc.php";
		include_once "admin-theme.php";
		include_once "admin-mustache.php";
		include_once COZYUNI_DIR . "cozyuni-admin-resync.php";
		include_once COZYUNI_DIR . "cozyuni-admin-bind.php";

		if (isset($_POST['genthemeinfo']) && check_admin_referer('genthemeinfo_clicked')) {
			mailrcpt_admin_generateThemeInfo();
		}
		if (isset($_POST['getmustachethemes']) && check_admin_referer('getmustachethemes_clicked')) {
			mailrcpt_admin_getMustacheThemes();
		}


		$cozyuni_apiKey = isset($_POST["cozyuni_apikey"]) ? sanitize_text_field($_POST["cozyuni_apikey"]) : cozyuni_getApiKey();
		$mailrcpt_email = isset($_POST["mailrcpt_email"]) ? sanitize_email($_POST["mailrcpt_email"]) : "";
		$mailrcpt_pass = isset($_POST["mailrcpt_pass"]) ? sanitize_text_field($_POST["mailrcpt_pass"]) : "";
		$cozyuni_wsPass = isset($_POST["cozyuni_wspass"]) ? sanitize_text_field($_POST["cozyuni_wspass"]) : "";//mailrcpt_getWsPass();
		if (isset($_POST['bind']) && check_admin_referer('bind_clicked')) {
			cozyuni_admin_bind(function () {
				cozyuni_setAdminMsg("Wordpress is now connected to MailRCPT. Api key and webservice password are also saved.");
			}, cozyuni_getErrorListener(), $cozyuni_apiKey, $mailrcpt_email, $mailrcpt_pass, $cozyuni_wsPass);
		}

		if (isset($_POST['unbind']) && check_admin_referer('unbind_clicked')) {
			$removeUsers = isset($_POST["removeusers"]) ? ($_POST["removeusers"] == "1" ? "1" : "0") : "0";
			cozyuni_admin_unbind(function () {
				delete_option("mailrcpt_page_start");
				delete_option("mailrcpt_page_middle");
				delete_option("mailrcpt_page_end");
				delete_option("mailrcpt_genThemeDone");
				cozyuni_setAdminMsg("Wordpress has been disconnected from MailRCPT.");
			}, cozyuni_getErrorListener(), $removeUsers);
		}


		$mailrcpt_templatetype = isset($_POST["mailrcpt_templatetype"]) ? sanitize_text_field($_POST["mailrcpt_templatetype"]) : get_option("mailrcpt_templatetype");
		$cozyuni_sitekey = isset($_POST["cozyuni_sitekey"]) ? sanitize_text_field($_POST["cozyuni_sitekey"]) : get_option("cozyuni_sitekey");
		$cozyuni_seckey = isset($_POST["cozyuni_seckey"]) ? sanitize_text_field($_POST["cozyuni_seckey"]) : get_option("cozyuni_seckey");
		if (isset($_POST['save']) && check_admin_referer('save_clicked')) {
			mailrcpt_admin_save($mailrcpt_templatetype, $cozyuni_sitekey, $cozyuni_seckey);
		}

		$mailrcpt_page_start = isset($_POST["mailrcpt_page_start"]) ? (_cozyuni_post("mailrcpt_page_start")) : get_option("mailrcpt_page_start");
		$mailrcpt_page_middle = isset($_POST["mailrcpt_page_middle"]) ? (_cozyuni_post("mailrcpt_page_middle")) : get_option("mailrcpt_page_middle");
		$mailrcpt_page_end = isset($_POST["mailrcpt_page_end"]) ? (_cozyuni_post("mailrcpt_page_end")) : get_option("mailrcpt_page_end");
		if (isset($_POST['generated']) && check_admin_referer('generated_clicked')) {
			mailrcpt_admin_generated_save($mailrcpt_page_start, $mailrcpt_page_middle, $mailrcpt_page_end);
		}

		if (isset($_POST['resync']) && check_admin_referer('resync_clicked')) {
			cozyuni_admin_resync();
		}

		$baseUrl = cozyuni_getBaseUrl();
		$savedApiKey = cozyuni_getApiKey();
		$connected = (!($baseUrl === false) && $savedApiKey != "");
		$resyncDone = get_option("cozyuni_resyncDone");
		$themeOk = false;
		$themeErrorMsg = "";
		if ($mailrcpt_templatetype == "page") {
			$pageThemeFile = get_template_directory() . "/page-mailrcpt.php";
			if (!file_exists($pageThemeFile)) {
				$themeErrorMsg = "Could not locate file: " . $pageThemeFile;
			} else {
				$themeOk = true;
			}
		} else if ($mailrcpt_templatetype == "generated") {
			$genThemeDone = get_option("mailrcpt_genThemeDone");
			if (!$genThemeDone) {
				$themeErrorMsg = "Please generate theme information";
			} else {
				$themeOk = true;
			}
		} else if ($mailrcpt_templatetype == "mustache") {
			$themeFile = get_template_directory() . "/header.mustache";
			$pageThemeFile = get_template_directory() . "/page-mailrcpt.php";
			if (!file_exists($themeFile)) {
				$themeErrorMsg = "No mustache templates founds, please retrieve them from MailRCPT";
			} else if (!file_exists($pageThemeFile)) {
				$themeErrorMsg = "Could not locate file: " . $pageThemeFile;
			} else {
				$themeOk = true;
			}
		}
		$permalinkEnabled = get_option('permalink_structure');

		?>
		<div class="wrap">
			<h1>MailRCPT settings</h1>


			<?php cozyuni_printAdminMsg() ?>

			<table id="main-table">
				<tr>
					<td>


						<table class="form-table">
							<tbody>
							<tr>
								<th>Setup status</th>
								<td>
									<!-- --------------------------------------- steps ------------------------------------------------------------ -->
									<ul>
										<?php
											$baseUrl = cozyuni_getBaseUrl();
											cozyuni_printStep("Connect to mailrcpt", $connected);
											cozyuni_printStep("reCAPTCHA keys", $cozyuni_sitekey != "" && $cozyuni_seckey != "");
											cozyuni_printStep("Initial resync", $resyncDone);
											cozyuni_printStep("Theme", $themeOk, $themeErrorMsg);
											cozyuni_printStep("Enable permalinks", $permalinkEnabled);
										?>
									</ul>
								</td>
							</tr>
							<tr>
								<th>Connect your Wordpress with MailRCPT</th>
								<td>
									<!-- --------------------------------------- connect ------------------------------------------------------------ -->
									<p>Fill out the form below and press the 'Connect' button to connect this Wordpress installation to

										<a href="http://www.mailrcpt.com" target="_blank">MailRCPT</a>.

										On success this will save your Api key and WebService password and will auto configure MailRCPT to work correctly with this Wordpress
										installation.</p>
									<p>Don't forget to 'Resync' after you are connected.</p>
									<form action="options-general.php?page=mailrcpt-options" method="post">
										<table class="form-table compact-form-table">
											<tr valign="top">
												<th scope="row">Connected</th>
												<td class="<?= $connected ? "cozyuni_done" : "cozyuni_notdone" ?>">
													<?php
														if ($connected) {
															$mailrcptOnWp = get_site_url() . "/mailrcpt/";
															?>
															Yes, with: <a href="<?= esc_url($baseUrl) ?>" target="_blank"><?= esc_url($baseUrl) ?></a>.
															<BR>                  MailRCP on your Wordpress: <a href="<?= $mailrcptOnWp ?>" target="_blank"><?= $mailrcptOnWp ?></a>
															<?php
														} else {
															print "No";
														}
													?>

												</td>
											</tr>
											<tr>
												<td colspan="2">
													<hr>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Api key</th>
												<td>
													<input type="text" name="cozyuni_apikey" value="<?= esc_attr($cozyuni_apiKey) ?>" class="regular-text" required>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Postmaster email address</th>
												<td>
													<input type="text" name="mailrcpt_email" value="<?= esc_attr($mailrcpt_email) ?>" class="regular-text" required>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Postmaster password</th>
												<td>
													<input type="password" name="mailrcpt_pass" value="<?= esc_attr($mailrcpt_pass) ?>" class="regular-text" required>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Webservice password</th>
												<td>
													<input type="password" name="cozyuni_wspass" value="<?= esc_attr($cozyuni_wsPass) ?>" class="regular-text" required>
												</td>
											</tr>
											<tr>
												<th></th>
												<td>
													<?php wp_nonce_field('bind_clicked'); ?>
													<input type="hidden" value="true" name="bind"/>
													<?php submit_button('Connect with MailRCPT') ?>
												</td>
											</tr>
										</table>


									</form>
								</td>
							</tr>
							<tr>
								<th>Settings</th>
								<td>
									<!-- --------------------------------------- settings ------------------------------------------------------------ -->
									<p>Below are a few settings you can change.</p>

									<form method="post" action="options-general.php?page=mailrcpt-options">

										<input type="radio" name="mailrcpt_templatetype" <?= $mailrcpt_templatetype == "generated" ? "checked" : "" ?> id="generated"
										       value="generated"> <label for="generated"><b>Auto-generated based on current theme</b></label>
										<BR>
										This plugin will try to generate a page template to use based on your current page template. Because Wordpress themes aren't standardized
										result may vary.
										<BR>
										<BR>

										<input type="radio" name="mailrcpt_templatetype" <?= $mailrcpt_templatetype == "page" ? "checked" : "" ?> id="page" value="page"> <label
											for="page"><b>Use 'page-mailrcpt.php' as page template</b></label>
										<BR>
										To use this option you will have to create your own page template named 'page-mailrcpt.php'. You can use function mailrcpt_pageTitle() to
										print the title and mailrcpt_pageContent() to print the content.
										<BR>
										<BR>

										<input type="radio" name="mailrcpt_templatetype" <?= $mailrcpt_templatetype == "mustache" ? "checked" : "" ?> id="mustache"
										       value="mustache"> <label for="mustache"><b>page-mailrcpt.php + mustache templates</b></label>
										<BR>
										This is the most flexible one. When selecting this option this plugin will use 'page-mailrcpt.php' (see previous option) and a collectioin
										of mustache templates which can be retrieved after selecting this option.
										<BR>
										<b>Note:</b> make sure, when picking this option, that you have enabled 'Output JSON in integration mode' in 'Site settings' in your
										MailRCPT dashboard.
										<BR>

										<?php wp_nonce_field('save_clicked'); ?>
										<input type="hidden" value="true" name="save"/>
										<?php submit_button('Save settings') ?>


									</form>
								</td>
							</tr>
							<?php if ($mailrcpt_templatetype == "generated") { ?>
								<tr>
									<th>Generate theme information</th>
									<td>
										<!-- --------------------------------------- theme info ------------------------------------------------------------ -->

										<p>MailRCPT can use your current theme. When you click the button below this plugin will analyze your theme and try to generate the correct
											theme information.</p>

										<p>If it fails you can always specify the theme information yourself.</p>
										<form action="options-general.php?page=mailrcpt-options" method="post">
											<?php wp_nonce_field('genthemeinfo_clicked'); ?>
											<input type="hidden" value="true" name="genthemeinfo"/>
											<?php submit_button('Generate theme information') ?>
										</form>
									</td>
								</tr>
							<?php } ?>
							<?php if ($mailrcpt_templatetype == "generated") { ?>
								<tr>
									<th>Customize generated theme information</th>
									<td>
										<!-- --------------------------------------- customize generated ------------------------------------------------------------ -->
										<p>Generating theme information can sometime fail, mainly because Wordpress themes aren't standardized. If you view MailRCPT on your blog
											and see something weird tuning the fields below might help.</p>

										<form method="post" action="options-general.php?page=mailrcpt-options">
											<table class="form-table compact-form-table">


												<tr valign="top">
													<th scope="row">Html between header and title</th>
													<td>
														<textarea name="mailrcpt_page_start" class="large-text code" rows="9"><?= ($mailrcpt_page_start) ?></textarea>
													</td>
												</tr>
												<tr valign="top">
													<th scope="row">Html between title and content</th>
													<td>
														<textarea name="mailrcpt_page_middle" class="large-text code" rows="9"><?= ($mailrcpt_page_middle) ?></textarea>
													</td>
												</tr>
												<tr valign="top">
													<th scope="row">Html between content and footer</th>
													<td>
														<textarea name="mailrcpt_page_end" class="large-text code" rows="9"><?= ($mailrcpt_page_end) ?></textarea>
													</td>
												</tr>

												<tr>
													<th></th>
													<td>
														<?php wp_nonce_field('generated_clicked'); ?>
														<input type="hidden" value="true" name="generated"/>
														<?php submit_button('Save') ?>
													</td>
												</tr>
											</table>

										</form>
									</td>
								</tr>
							<?php } ?>
							<?php if ($mailrcpt_templatetype == "mustache") { ?>
								<tr>
									<th>Get mustache themes</th>
									<td>
										<!-- --------------------------------------- get mustache themes ------------------------------------------------------------ -->


										<h2 class="title"></h2>

										<p>This will retrieved all needed Mustache templates from MailRCPT and save it to your theme directory.</p>

										<p>Note: this will overwrite any mustache templates including any changes your might have made.</p>

										<form action="options-general.php?page=mailrcpt-options" method="post">
											<?php wp_nonce_field('getmustachethemes_clicked'); ?>
											<input type="hidden" value="true" name="getmustachethemes"/>
											<?php submit_button('Get &amp; Save mustache themes') ?>
										</form>

									</td>
								</tr>
							<?php } ?>
							<tr>
								<th>Resync</th>
								<td>
									<!-- --------------------------------------- resync ------------------------------------------------------------ -->

									<p>If you click on the 'Resync' button all your Wordpress users will be synchronized with MailRCPT.</p>
									<form action="options-general.php?page=mailrcpt-options" method="post">
										<?php wp_nonce_field('resync_clicked'); ?>
										<input type="hidden" value="true" name="resync"/>
										<?php submit_button('Resync') ?>
									</form>
								</td>
							</tr>
							<tr>
								<th>Disconnect</th>
								<td>
									<!-- --------------------------------------- disconnect ------------------------------------------------------------ -->

									<p>If you don't want to use MailRCPT with this Wordpress installation anymore than please click the Disconnect button below.</p>

									<p>Only check the 'Remove users' checkbox if you never ever plan on using MailRCPT again with this wordpress blog.</p>

									<p>Note that you can always 'Connect' again.</p>
									<form action="options-general.php?page=mailrcpt-options" method="post">
										<table class="form-table compact-form-table">
											<tr valign="top">
												<th scope="row">&nbsp;</th>
												<td>
													<input type="checkbox" name="removeusers" id="removeusers" value="1"> <label for="removeusers">Remove users in MailRCPT</label>
												</td>
											</tr>
											<tr>
												<th></th>
												<td>
													<?php wp_nonce_field('unbind_clicked'); ?>
													<input type="hidden" value="true" name="unbind"/>
													<?php submit_button('Disconnect from MailRCPT') ?>
												</td>
											</tr>
										</table>

									</form>
								</td>
							</tr>

							</tbody>
						</table>
					</td>
					<td width="200">
						<div id="cozyuni_brand">
							<div align="center" style="background-color: black;"><a href="http://www.mailrcpt.com/" target="_blank"><img
										src="<?= plugins_url("logo.png", __FILE__) ?>"></a></div>
							<hr>
							<b>Handy links</b>
							<BR>
							<a href="http://www.mailrcpt.com/" target="_blank">MailRCPT</a>
							<BR>
							<a href="http://www.mailrcpt.com/dash/preaccount/add" target="_blank">Create a free account</a>
							<BR>
							<a href="http://official.mailrcpt.com/pages/wordpress-plugin-installation.html" target="_blank">Plugin installation help</a>
							<BR>
							<a href="http://mailrcpt.forumlines.com/" target="_blank">Support forum</a>
							<BR>
							<b>Tips</b>
							<BR>
							* A 'Mailings' widget is available.
							<BR>

						</div>
					</td>
				</tr>
			</table>


		</div>

		<!-- --------------------------------------- css ------------------------------------------------------------ -->

		<?php
		cozyuni_printAdminCss();
	}



