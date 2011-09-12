<?php

class ProfileModule extends BaseModule {

	public $id;

	// даже если берем данные из кеша, нужно указать, какой view используется
	function getXSLTFileNameView($ignoreXHTML = false) {
		$params = $this->processParams();
		if ($params['action'])
			return $this->props['xslt'] . '_' . $params['action'];
		else
			return $this->props['xslt'];
	}

	function processParams() {
		$mask = array(
		    'id' => 'int',
		    'action' => array(
			'type' => 'string',
			'*' => true
		    )
		);
		$params = Request::checkParameters(Request::getAllParameters(), $mask);
		return $params;
	}

	function generateData() {
		global $current_user;
		$params = $this->processParams();
		$this->id = $params['id'];
		switch ($params['action']) {
			case 'edit':
				if ($current_user->id != $this->id)
					throw new Exception('Access Denied', Error::E_WRONG_ROLE);
				$this->generateProfile();
				break;
			case 'friends':
				$this->generateFriends();
				break;
			case '':
				$this->generateProfile();
				break;
			default:
				// просматриваем профиль пользователя
				$this->generateProfile();
				break;
		}
	}

	function generateFriends() {
		global $current_user;
		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);
		
		$this->data['profile']['id'] = $user->id;
		$this->data['profile']['nickname'] = $user->getProperty('nickname');

		// можно добавить в друзья?
		$followingids = $user->getFollowing();
		$followingUsers = $this->getDataFromUids($followingids);
		$this->data['profile']['following'] = $followingUsers;
		// 
		$followersids = $user->getFollowers();
		$followersUsers = $this->getDataFromUids($followersids);
		$this->data['profile']['followers'] = $followersUsers;
	}

	function getDataFromUids($ids) {
		if (!count($ids))
			return array();
		$out = array();
		$query = 'SELECT * FROM `users` WHERE `id` IN (' . implode(',', $ids) . ')';
		$result = Database::sql2array($query);
		foreach ($result as $userRow) {
			$user = Users::getById($userRow['id'], $userRow);
			/* @var $user User */
			$out[$user->id] = array(
			    'id' => $user->id,
			    'nickname' => $user->getProperty('nickname'),
			    'picture' => $user->getProperty('picture') ? $user->id . '.jpg' : 'default.jpg',
			    'lastSave' => $user->getProperty('lastSave'),
			);
		}
		return $out;
	}

	function generateProfile() {
		global $current_user;
		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);


		$this->data['profile'] = $user->getXMLInfo();


		$this->data['profile']['role'] = $user->getRole();
		$this->data['profile']['lang'] = $user->getLanguage();
		$this->data['profile']['city_id'] = $user->getProperty('city_id');
		$this->data['profile']['city'] = Database::sql2single('SELECT `name` FROM `lib_city` WHERE `id`=' . $user->getProperty('city_id'));
		$this->data['profile']['picture'] = $user->getProperty('picture') ? $user->id . '.jpg' : 'default.jpg';
		$this->data['profile']['rolename'] = $user->getRoleName();
		$this->data['profile']['bday'] = $user->getBday(date('d-m-Y'), 'd-m-Y');

		$this->data['profile']['bdays'] = $user->getBday('неизвестно', 'd.m.Y');
		// additional
		$this->data['profile']['link_fb'] = $user->getPropertySerialized('link_fb');
		$this->data['profile']['link_vk'] = $user->getPropertySerialized('link_vk');
		$this->data['profile']['link_tw'] = $user->getPropertySerialized('link_tw');
		$this->data['profile']['link_lj'] = $user->getPropertySerialized('link_lj');
	}

}