<?php

class EmailConfirmWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$mask = array(
		    'id' => 'int',
		    'hash' => array(
			'type' => 'string',
			'regexp' => '/^[A-Za-z0-9]+$/',
			'min_length' => 32,
			'max_length' => 32,
		    ),
		);
		$params = Request::checkParameters(Request::getAllParameters(), $mask);

		// проверяем, есть ли в базе неподтвержденный юзер с таким хешем
		$query = 'SELECT * FROM `users` WHERE `id`=' . $params['id'];
		$res = Database::sql2row($query);
		if (!$res || ($res['hash'] != $params['hash'])) {
			if ($res['hash'] != '')
				$this->setWriteParameter('EmailConfirmModule', 'error', 'illegal_hash');
			else if($res['id'])
				$this->setWriteParameter('EmailConfirmModule', 'error', 'already_confirmed');
			else
				$this->setWriteParameter('EmailConfirmModule', 'error', 'no_user');
		}else{
			// ура! авторизуем пользователя
			$current_user->load($res);
			$current_user->setRole(User::ROLE_READER_CONFIRMED);
			$current_user->authorized = true;
			$current_user->onLogin();
			$current_user->save();
			// затираем ему хеш и меняем роль на авторизованного пользователя
			
			$this->setWriteParameter('EmailConfirmModule', 'success', 1);
		}
	}

}