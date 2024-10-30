<?php
	/**
	 * CozyUni for Wordpress; cozyuni.com
	 */

	if (!class_exists('cozyuni_UserRest')) {
		class cozyuni_UserRest implements JsonSerializable {
			private $externalId;
			private $username;
			private $email;
			private $pass;
			private $title;
			private $roles;

			public function __construct($externalId, $username, $email, $pass, $title, $roles) {
				$this->externalId = $externalId;
				$this->username = $username;
				$this->email = $email;
				$this->pass = $pass;
				$this->title = $title;
				$this->roles = $roles;
			}

			public function getExternalId() {
				return $this->externalId;
			}

			public function getUsername() {
				return $this->username;
			}

			public function getEmail() {
				return $this->email;
			}

			public function getPass() {
				return $this->pass;
			}

			public function getTitle() {
				return $this->title;
			}

			public function getRoles() {
				return $this->roles;
			}

			public function jsonSerialize() {
				return get_object_vars($this);
			}

			public function set($data) {
				foreach ($data AS $key => $value) {
					$this->{$key} = $value;
				}
			}

		}
	}


	if (!class_exists('cozyuni_UserListRest')) {
		class cozyuni_UserListRest implements JsonSerializable {
			private $users;

			public function __construct($users) {
				$this->users = $users;
			}

			public function getUsers() {
				return $this->users;
			}

			public function jsonSerialize() {
				return get_object_vars($this);
			}
		}
	}

	if (!class_exists('cozyuni_IntModeBindReq')) {
		class cozyuni_IntModeBindReq implements JsonSerializable {
			private $email;
			private $pass;
			private $wpUrl;
			private $wsPass;


			public function __construct($email, $pass, $wpUrl, $wsPass) {
				$this->email = $email;
				$this->pass = $pass;
				$this->wpUrl = $wpUrl;
				$this->wsPass = $wsPass;
			}

			public function getEmail() {
				return $this->email;
			}

			public function getPass() {
				return $this->pass;
			}

			public function getWpUrl() {
				return $this->wpUrl;
			}

			public function getWsPass() {
				return $this->wsPass;
			}

			public function jsonSerialize() {
				return get_object_vars($this);
			}
		}
	}

	if (!class_exists('cozyuni_IntModeUnbindReq')) {
		class cozyuni_IntModeUnbindReq implements JsonSerializable {
			private $removeUsers;

			public function __construct($removeUsers) {
				$this->removeUsers = $removeUsers;
			}

			public function getRemoveUsers() {
				return $this->removeUsers;
			}

			public function jsonSerialize() {
				return get_object_vars($this);
			}
		}
	}

