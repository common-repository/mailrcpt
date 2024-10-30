<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!function_exists('cozyuni_toUserRest')) {
		function cozyuni_toUserRest($user) {
			return new cozyuni_UserRest($user->ID, $user->user_login, $user->user_email, $user->user_pass, $user->display_name, implode(',', $user->roles));
		}
	}

	if (!function_exists('cozyuni_syncUpdateUser')) {
		function cozyuni_syncUpdateUser($user) {
			$userRest = cozyuni_toUserRest($user);
			cozyuni_remote_post("/sync/update", $userRest);
		}
	}

	if (!function_exists('cozyuni_password_reset')) {
		add_action('password_reset', 'cozyuni_password_reset', 10, 2);
		function cozyuni_password_reset($user, $new_pass) {
			write_log("cozyuni_password_reset> user.ID: " . $user->ID);

			if (!cozyuni_isSubscriber($user)) {
				return;
			}

			cozyuni_syncUpdateUser($user);
		}
	}

	if (!function_exists('cozyuni_user_register')) {
		add_action('user_register', 'cozyuni_user_register', 10, 1);
		function cozyuni_user_register($user_id) {
			write_log("cozyuni_user_register> user_id: " . $user_id);

			$user = get_user_by('id', $user_id);
			if (!cozyuni_isSubscriber($user)) {
				return;
			}

			cozyuni_syncUpdateUser($user);
		}
	}

	if (!function_exists('cozyuni_profile_update')) {
		add_action('profile_update', 'cozyuni_profile_update', 10, 2);
		function cozyuni_profile_update($user_id, $old_user_data) {
			write_log("cozyuni_profile_update> user_id: " . $user_id);

			$user = get_user_by('id', $user_id);
			if (!cozyuni_isSubscriber($user)) {
				return;
			}

			cozyuni_syncUpdateUser($user);
		}
	}

	if (!function_exists('cozyuni_delete_user')) {
		add_action('delete_user', 'cozyuni_delete_user');
		function cozyuni_delete_user($user_id) {
			write_log("cozyuni_delete_user> user_id: " . $user_id);

			$user = get_user_by('id', $user_id);
			if (!cozyuni_isSubscriber($user)) {
				return;
			}

			$userRest = cozyuni_toUserRest($user);
			cozyuni_remote_post("/sync/remove", $userRest);
		}
	}
