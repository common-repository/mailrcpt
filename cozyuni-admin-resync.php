<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!function_exists('cozyuni_admin_resync')) {
		function cozyuni_admin_resync() {
			write_log("cozyuni_admin_resync");

			$users = get_users("role=subscriber");
			$userArray = array();
			foreach ($users as $user) {
				if (!cozyuni_isSubscriber($user)) {
					continue;
				}
				$userRest = cozyuni_toUserRest($user);
				array_push($userArray, $userRest);
			}

			$userList = new cozyuni_UserListRest($userArray);
			$userListResp = cozyuni_remote_post("/sync/resync", $userList);

			update_option("cozyuni_resyncDone", true);

			cozyuni_setAdminMsg("Resynced " . $userListResp . " user(s)");
		}
	}