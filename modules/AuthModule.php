<?php
class AuthModule extends BaseModule {
	public $user;

	/**
	 * чтобы закешировать ноду юзера, нужно чтобы этот юзер не был авторизован
	 * авторизованных юзеров хранить в кеше не будем
	 */
	function checkCacheSettings() {
		global $current_user;
		if (parent::checkCacheSettings()) {
			if ($current_user->authorized) {
				// отключаем кеширование
				parent::disableCaching();
				return false;
			}
		}
		// вполне можно покешировать пустую ноду
		return true;
	}

	function generateData() {
		global $current_user;
		$this->data['profile']['authorized'] = 0;
		if ($current_user->authorized) {
			// авторизован
			$this->data['profile'] = $current_user->getXMLInfo();
			$this->data['profile']['authorized'] = 1;
		}
	}

}