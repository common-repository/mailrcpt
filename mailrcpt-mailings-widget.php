<?php
	/**
	 * MailRCPT for Wordpress; mailrcpt.com
	 */

	class MailRCPT_Mailings_Widget extends WP_Widget {
		function __construct() {
			parent::__construct(
				'mailrcpt_mailings_widget', // Base ID
				esc_html__('Mailings'), // Name
				array('description' => esc_html__('MailRCPT mailings'),) // Args
			);
		}

		public function widget($args, $instance) {
			$mailings = cozyuni_getCached("mailrcpt_mailings");

			if (!is_array($mailings)) {
				$mlFolder = $instance['folder'];
				$max = $instance['max'];
				$errorMsg = "";

				$resp = cozyuni_remote_get("/api/mailings?mlFolder=" . $mlFolder . "&max=" . $max);
				if (!is_array($resp)) {
					$errorMsg = $resp;
				} else {
					$mailings = $resp;
					cozyuni_setCached("mailrcpt_mailings", $mailings);
				}
			}
			print $args['before_widget'];

			if (!empty($instance['title'])) {
				print $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
			}

			if (!empty($errorMsg)) {
				print $errorMsg;
			} else {
				?>
				<ul>
					<?php
						foreach ($mailings as $mailing) {
							?>
							<li><a href="/mailrcpt<?= $mailing["relativeUrl"] ?>"><?= $mailing["title"] ?></a></li>
							<?php
						}
					?>
				</ul>
				<?php
			}

			print $args['after_widget'];
		}


		public function form($instance) {
			$title = !empty($instance['title']) ? $instance['title'] : esc_html__('Mailings');
			$folder = !empty($instance['folder']) ? $instance['folder'] : esc_html__('');
			$max = !empty($instance['max']) ? $instance['max'] : esc_html__('25');
			?>
			<p>
				<label for="<?= esc_attr($this->get_field_id('title')); ?>"><?= esc_attr_e('Title:'); ?></label>

				<input class="widefat" id="<?= esc_attr($this->get_field_id('title')); ?>" name="<?= esc_attr($this->get_field_name('title')); ?>" type="text"
				       value="<?= esc_attr($title); ?>">
			</p>

			<p>
				<label for="<?= esc_attr($this->get_field_id('folder')); ?>"><?= esc_attr_e('Mailing list folder:'); ?></label>

				<input class="widefat" id="<?= esc_attr($this->get_field_id('folder')); ?>" name="<?= esc_attr($this->get_field_name('folder')); ?>" type="text"
				       value="<?= esc_attr($folder); ?>">
			</p>

			<p>
				<label for="<?= esc_attr($this->get_field_id('max')); ?>"><?= esc_attr_e('Number of mailings:'); ?></label>

				<input class="widefat" id="<?= esc_attr($this->get_field_id('max')); ?>" name="<?= esc_attr($this->get_field_name('max')); ?>" type="text"
				       value="<?= esc_attr($max); ?>">
			</p>
			<?php
		}


		public function update($new_instance, $old_instance) {
			$instance = array();
			$instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
			$instance['folder'] = (!empty($new_instance['folder'])) ? sanitize_text_field($new_instance['folder']) : '';
			$instance['max'] = (!empty($new_instance['max']) && is_numeric($new_instance['max'])) ? sanitize_text_field($new_instance['max']) : '25';
			cozyuni_clearCached("mailrcpt_mailings");

			return $instance;
		}

	}