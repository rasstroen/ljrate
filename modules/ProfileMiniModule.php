<?php

class ProfileMiniModule extends BaseModule {

	function generateData() {
		global $current_user;
		// мини блок с меню пользователя и его инфо
		// если страница user/id и это не наша страница - рисуем чужого пользователя
		if (Request::$pageName == 'user') {
			$id = Request::get(0, false);
		}else
			$id = $current_user->id;

		if ($id && ($id == $current_user->id)) {
			$user = $current_user;
		} else if ($id) {
			$user = Users::getById($id);
		}
		if (!$id)
			return false;
		/* @var $user User */
		$this->data['profile']['id'] = $user->id;
		$this->data['profile']['nickname'] = $user->getProperty('nickname');
		$this->data['profile']['rolename'] = $user->getRoleName();
		$this->data['profile']['picture'] = $user->getProperty('picture') ? $user->id . '.jpg' : 'default.jpg';
	}

	function checkCacheSettings() {
		global $current_user;
		// только если это чужой миниблок
		if (Request::$pageName == 'user') {
			$id = Request::get(0, false);
			if ($id != $current_user->id) {
				$this->cache_enabled = true;
			}
		}

		if ((isset($this->settings['cache']) && $this->settings['cache']) || (isset($this->props['params']['cache']) && $this->props['params']['cache'] )) {
			$this->xml_cache_name = Request::$pageName . '_' . $this->moduleName . '_' . (implode('', Request::getAllParameters()));
			if (isset($this->settings['xHTML']) && $this->settings['xHTML']) {
				if ((Request::$responseType == 'xsl') || (Request::$responseType == 'xml')) { // при просмотре xml и xslt отрубаем кеширование
					$this->cache_enabled = false;
					$this->xHTMLCachingEnabled = false;
				}else
					$this->xHTMLCachingEnabled = true;
			}
		}
		return $this->cache_enabled;
	}

}