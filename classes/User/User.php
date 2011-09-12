<?php

// класс, отвечающий за юзера
class User {
	const ROLE_ANON = 0; // аноним
	const ROLE_READER_UNCONFIRMED = 1; // юзер с неподтвержденным мылом
	const ROLE_READER_CONFIRMED = 2; // юзер с подтвержденным мылом

	const ROLE_SITE_ADMIN = 20; // юзер с подтвержденным мылом

	public $id = 0;
	// users
	public $changed = array();
	public $profile = array();
	public $loaded;
	//users_additional
	public $profileSerialized = array();
	public $changedSerialized = array();
	public $loadedAdditional;
	//
	public $profile_xml = array();
	public $xml_fields = array(
	    'id',
	    'nickname',
	    'lastSave',
	    'lastLogin',
	);

	// кто меня читает
	function setFollowers(array $array) {
		$this->loadAdditional();
		$this->profileSerialized['followers'] = $array;
	}

	// кого я читаю
	function setFollowing(array $array) {
		$this->loadAdditional();
		$this->profileSerialized['following'] = $array;
	}

	// вернуть тех, кого я читаю
	function getFollowing() {
		$this->loadAdditional();
		return $this->profileSerialized['following'];
	}

	// вернуть всех, кто меня читает
	function getFollowers() {
		$this->loadAdditional();
		return $this->profileSerialized['followers'];
	}

	// когда юзера зафрендили
	function onNewFollower() {
		
	}

	// когда юзер зафрендил кого-либо
	function onNewFollowing() {
		
	}

	function __construct($id = false, $data = false) {
		$this->loaded = false;
		if ($id) {
			$this->id = max(0, $id);
		}
		if ($data)
			$this->load($data);
	}

	public function getTheme() {
		return Config::need('default_theme');
	}

	public function getLanguage() {
		return Config::need('default_language');
	}

	function register($nickname, $email, $password) {
		$hash = md5($email . $nickname . $password . time());
		$query = 'INSERT INTO `users` SET
			`email`=\'' . $email . '\',
			`password`=\'' . md5($password) . '\',
			`nickname`=\'' . $nickname . '\',
			`hash` = \'' . $hash . '\'';
		if (Database::query($query)) {
			$this->id = Database::lastInsertId();
			if ($this->id) {
				return $hash;
			}
		}
		return false;
	}

	// отправляем в xml информацию о пользователе
	public function setXMLAttibute($field, $value) {
		if (in_array($field, $this->xml_fields))
			$this->profile_xml[$field] = $value;
	}

	// отдаем информацию по пользователю для отображения в xml
	public function getXMLInfo() {
		$this->load();
		return $this->profile_xml;
	}

	// грузим дополнительню информацию
	public function loadAdditional($rowData = false) {
		if ($this->loadedAdditional)
			return true;
		$this->loadedAdditional = true;
		if (!$rowData) {
			$rowData = Database::sql2row('SELECT * FROM `users_additional` WHERE `id_user`=' . $this->id);
		}
		if (!$rowData) {
			$rowData = array('serialized' => '', 'followers' => '', 'following' => '');
		}

		foreach ($rowData as $field => $value) {
			if ($field == 'serialized') {
				$value = @json_decode($value, true);
				if (is_array($value))
					foreach ($value as $f => $v)
						$this->setPropertySerialized($f, $v);
			} else
			if (in_array($field, array('followers', 'following'))) {
				$value = $value ? explode(',', $value) : array();
				$value_normalized = array();
				if (count($value))
					foreach ($value as $id)
						$value_normalized[$id] = $id;
				$this->setPropertySerialized($field, $value_normalized);
			}
		}
		$this->additionalRaw = $rowData;
		return;
	}

	// грузим информацию по пользователю
	public function load($rowData = false) {
		if ($this->loaded)
			return true;
		$this->loaded = true;
		if (!$rowData) {
			if (!$this->id) {
				$this->setXMLAttibute('auth', 0);
			} else {
				$rowData = Database::sql2row('SELECT * FROM `users` WHERE `id`=' . $this->id);
			}
		}
		if (!$rowData) {
			// нет юзера в базе
			throw new Exception('No user #' . $this->id . ' in database', Error::E_USER_NOT_FOUND);
		}

		$this->id = (int) $rowData['id'];

		foreach ($rowData as $field => $value) {
			if ($field == 'serialized') {
				$arr = json_decode($value, true);
				if (is_array($arr))
					foreach ($arr as $field => $value) {
						$this->setPropertySerialized($field, $value, $save = false);
						$this->setXMLAttibute($field, $value);
					}
			}
			// все данные в profile
			$this->setProperty($field, $value, $save = false);
			// данные для xml - в xml
			$this->setXMLAttibute($field, $value);
		}
		return;
	}

	public function setRole($role) {
		$this->setProperty('role', $role);
		$this->setProperty('hash', '');
	}

	public function getRole() {
		return (int) $this->getProperty('role');
	}

	public function getBdayString($default = 'неизвестно') {
		if ($this->getProperty('bday')) {
			
		} else {
			return $default;
		}
	}

	public function getBday($default = 0, $format = 'Y-m-d') {
		return date($format, $this->getProperty('bday', $default));
	}

	public function getRoleName($id = false) {
		if (!$id)
			$id = $this->getRole();
		return Users::$rolenames[$id];
	}

	public function setPropertySerialized($field, $value, $save = true) {
		$this->loadAdditional();
		if (!$save)
			$this->profileSerialized[$field] = $value;
		else
			$this->profileSerialized[$field] = $this->changedSerialized[$field] = $value;
	}

	public function setProperty($field, $value, $save = true) {
		if (!$save)
			$this->profile[$field] = $value;
		else
			$this->profile[$field] = $this->changed[$field] = $value;
	}

	public function getProperty($field, $default = false) {
		$this->load();
		return isset($this->profile[$field]) ? $this->profile[$field] : $default;
	}

	public function getPropertySerialized($field, $default = false) {
		$this->loadAdditional();
		return isset($this->profileSerialized[$field]) ? $this->profileSerialized[$field] : $default;
	}

	function __destruct() {
		if (count($this->changed)) {
			
		}
	}

	function save() {
		// дополнительные поля
		if (count($this->changedSerialized) && $this->id) {
			$profileSerialized = array();
			foreach ($this->profileSerialized as $field => $value) {
				if (!in_array($field, array('followers', 'following'))) {
					$profileSerialized[$field] = $value;
				}
			}
			$followers = implode(',', $this->profileSerialized['followers']);
			$following = implode(',', $this->profileSerialized['following']);
			$query = 'INSERT INTO `users_additional` SET 
				`id_user`=' . $this->id . ',
				`serialized` = \'' . mysql_escape_string(json_encode($profileSerialized)) . '\',
				`followers` = \'' . $followers . '\',
				`following` = \'' . $following . '\'
					ON DUPLICATE KEY UPDATE
				`serialized` = \'' . mysql_escape_string(json_encode($profileSerialized)) . '\',
				`followers` = \'' . $followers . '\',
				`following` = \'' . $following . '\'';
			Database::query($query);
		}
		// основные поля
		if (count($this->changed) && $this->id) {
			$this->changed['lastSave'] = time();
			foreach ($this->changed as $f => $v)
				$sqlparts[] = '`' . $f . '`=\'' . mysql_escape_string($v) . '\'';
			$sqlparts = implode(',', $sqlparts);
			$query = 'INSERT INTO `users` SET `id`=' . $this->id . ',' . $sqlparts . ' ON DUPLICATE KEY UPDATE ' . $sqlparts;

			Database::query($query);
		}
	}

}