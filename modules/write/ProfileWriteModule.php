<?php

class ProfileWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if ($current_user->authorized) {
			$mask = array(
			    'id' => 'int',
			    'bday' => 'string',
			    'city_id' => 'int',
			    'link_fb' => array(
				'type' => 'string',
				'*' => true,
			    ),
			    'link_vk' => array(
				'type' => 'string',
				'*' => true,
			    ),
			    'link_lj' => array(
				'type' => 'string',
				'*' => true,
			    ),
			    'link_tw' => array(
				'type' => 'string',
				'*' => true,
			    )
			);
			$params = Request::checkPostParameters($mask);


			if ($current_user->id == $params['id']) {
				//avatar
				if (isset($_FILES['picture']) && $_FILES['picture']['tmp_name']) {
					$filename = Config::need('avatar_upload_path') . '/' . $current_user->id . '.jpg';
					$upload = new UploadAvatar($_FILES['picture']['tmp_name'], 100, 100, "simple", $filename);
					if ($upload->out)
						$current_user->setProperty('picture', 1);
				}
				//bday
				$current_user->setProperty('bday', strtotime($params['bday']));
				// city
				$current_user->setProperty('city_id', $params['city_id']);
				// facebook etc
				$current_user->setPropertySerialized('link_fb', $params['link_fb']);
				$current_user->setPropertySerialized('link_vk', $params['link_vk']);
				$current_user->setPropertySerialized('link_tw', $params['link_tw']);
				$current_user->setPropertySerialized('link_lj', $params['link_lj']);

				$current_user->save();
				// после редактирования профиля надо посбрасывать кеш со страницы профиля
				// и со страницы редактирования профиля
				// кеш в остальных модулях истечет сам
				Cache::drop(Request::$pageName . '_ProfileModule_' . $current_user->id, Cache::DATA_TYPE_XML); //xmlthemeDefault_ru_user_ProfileModule
				Cache::drop(Request::$pageName . '_ProfileModule_' . $current_user->id . 'edit', Cache::DATA_TYPE_XML); //xmlthemeDefault_ru_user_ProfileModule_19
			}
		}
	}

}