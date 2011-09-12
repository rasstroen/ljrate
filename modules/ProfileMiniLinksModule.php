<?php

class ProfileMiniLinksModule extends BaseModule {

	function generateData() {
		global $current_user;
		// мини блок с меню пользователя и его инфо
		// если страница user/id и это не наша страница - рисуем чужого пользователя
		$id = Request::get(0, false);
		if (!$id)
			return false;

		if ($id == $current_user->id) {
			return false;
		} else {
			$user = Users::getById($id);
		}

		/* @var $user User */
		/* @var $current_user CurrentUser */
		// выдаем данные по пользователю
		$this->data['profile']['id'] = $user->id;
		// можно добавить в друзья?
		if (in_array($user->id, $current_user->getFollowing())) {
			$this->data['profile']['following'] = 1;
		} else {
			$this->data['profile']['following'] = 0;
		}
	}

}