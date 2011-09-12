<?php

class BackendModule extends BaseModule {

	/**
	 *  @property CurrentUser $user
	 */
	public $user;

	/**
	 * чтобы закешировать ноду юзера, нужно чтобы этот юзер не был авторизован
	 * авторизованных юзеров хранить в кеше не будем
	 */
	function generateData() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if ($current_user->getRole() !== User::ROLE_SITE_ADMIN) {
			throw new Exception('Access');
		}
		if (Request::get(1) == 'generate') {
			$this->generateLibrary();
		}
		$this->data['page'] = Request::get(0, 'pages');
		switch ($this->data['page']) {
			case 'pages':
				$this->moduleList();
				$this->getPages();
				$this->data['roles'] = $this->getRolesFromString(false);
				break;
			case 'modules':
				$this->getModules();
				break;
		}
		$this->data['blocks'] = Database::sql2array('SELECT * FROM `core_blocks`');
	}

	function moduleList() {
		$query = 'SELECT * FROM `core_modules`';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$this->data['moduleList'][] = $pagerow;
		}
	}

	function getPages() {
		$query = 'SELECT * FROM `core_pages`';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$pagerow['modules'] = $this->getModules($pagerow['id']);
			$this->data['pages'][] = $pagerow;
		}
		$this->data['inherited_modules'] = $this->getInheritedModules(0);
	}

	function getInheritedModules() {
		$out = array();

		$query = 'SELECT * FROM `core_modules` WHERE inherited=1';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$out[] = $pagerow;
		}
		return $out;
	}
	
	function getRolesFromString($s){
		$out = array();
		if(!$s) {
			foreach(Users::$rolenames as $id => $name)
			$out[$id] = array('id'=>$id , 'name' => $name);
			return $out;
		}
		$ids = explode(',', $s);
		foreach($ids as $id){
			$out[$id] = array('id'=>$id , 'name' => Users::$rolenames[$id]);
		}
		return $out;
	}

	function getModules($id_page = false) {
		$out = array();
		if ($id_page === false)
			$query = 'SELECT * FROM `core_modules`';
		else
			$query = 'SELECT * FROM `core_modules` CM
				JOIN `core_pages_modules` CPM 
				ON (CPM.id_module = CM.id AND CPM.id_page=' . $id_page . ')';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			if ($id_page === false){
				$this->data['modules'][] = $pagerow;
			}
			else{
				$pagerow['roles'] = $this->getRolesFromString($pagerow['roles']);
				$out[] = $pagerow;
			}
				
		}
		return $out;
	}
	
	function genRoles($ids){
		$out = array();
		foreach ($ids as $id){
			$out[$id] = $id;
		}
		return $out;
	}

	function generateLibrary() {
		$phplib_pages = Config::need('phplib_pages_path');
		$phplib_modules = Config::need('phplib_modules_path');
		// генерируем модули
		$phplib_pages.='/LibPages.php';
		$phplib_modules.='/LibModules.php';

		$pages = Database::sql2array('SELECT * FROM `core_pages`', 'id');
		$modules = Database::sql2array('SELECT * FROM `core_modules`', 'id');
		$pages_modules = Database::sql2array('SELECT * FROM `core_pages_modules`');
		$module_block = array();
		$module_roles = array();
		$pages_modules_prepared = array();
		foreach ($pages_modules as $pm) {
			if ($pm['enabled'] == 1)
				$pages_modules_prepared[$pm['id_page']][$modules[$pm['id_module']]['name']] = array($modules[$pm['id_module']]['name'] => array());
			else
				$pages_modules_prepared_d[$pm['id_page']][$modules[$pm['id_module']]['name']] = $modules[$pm['id_module']]['name'];
			$module_block[$pm['id_page']][$modules[$pm['id_module']]['name']] = $pm['block'];
			$module_roles[$pm['id_page']][$modules[$pm['id_module']]['name']] = $this->genRoles(explode(',',$pm['roles']));
		}
		


		// LibPages.php
		$pagesClass = array();
		$pageParams = array();
		$blocknames = Database::sql2array('SELECT * FROM `core_blocks`', 'id');
		foreach ($pages as $page) {
			$modules_current = array();
			if (isset($pages_modules_prepared[$page['id']]))
				foreach ($pages_modules_prepared[$page['id']] as $module_name => $moduleSettings) {
					$modules_current[$module_name] = $moduleSettings[$module_name];
					$modules_current[$module_name]['block'] = $blocknames[$module_block[$page['id']][$module_name]]['name'];
					
					if(isset($module_roles[$page['id']][$module_name]))
					$modules_current[$module_name]['roles'] = $module_roles[$page['id']][$module_name];
				}
			if ($page['cache_sec']) {
				$pageParams['cache'] = true;
				$pageParams['cache_sec'] = (int) $page['cache_sec'];
			} else {
				$pageParams['cache'] = false;
				$pageParams['cache_sec'] = 0;
			}


			$pagesClass['pages'][$page['name']] = array(
			    'title' => $page['title'],
			    'name' => $page['name'],
			    'params' => $pageParams,
			    'xslt' => $page['xslt'],
			    'modules' => $modules_current,
			    'modules_deprecated' => isset($pages_modules_prepared_d[$page['id']]) ? $pages_modules_prepared_d[$page['id']] : array(),
			);
		}

		$phplib_pages_s = '<?php  /* GENERATED AUTOMATICALLY AT ' . date('Y-m-d H:i:s') . ', DO NOT MODIFY */' . "\n" . 'class LibPages{';
		foreach ($pagesClass as $property => $value) {
			$phplib_pages_s.= "\n" . 'public static $' . $property . ' = ' . "\n";
			$phplib_pages_s.=var_export($value, 1) . ';';
		}
		$phplib_pages_s.="\n" . '}';

		file_put_contents($phplib_pages, $phplib_pages_s);

		// LibModules.php
		$modulesClass = array();
		$moduleParams = array();

		foreach ($modules as $id => $module) {
			if ($module['cache_sec']) {
				$moduleParams['cache_sec'] = (int) $module['cache_sec'];
				$moduleParams['cache'] = true;
				$moduleParams['xHTML'] = (int) $module['xHTML'] ? true : false;
			} else {
				$moduleParams['cache_sec'] = false;
				$moduleParams['cache'] = false;
				$moduleParams['xHTML'] = false;
			}
			$modulesClass['modules'][$module['name']] = array(
			    'name' => $module['name'],
			    'xslt' => $module['xslt'],
			    'views' => array(),
			    'params' => $moduleParams,
			    'inherited' => $module['inherited'],
			);
		}

		$phplib_modules_s = '<?php  /* GENERATED AUTOMATICALLY AT ' . date('Y-m-d H:i:s') . ', DO NOT MODIFY */' . "\n" . 'class LibModules{';
		foreach ($modulesClass as $property => $value) {
			$phplib_modules_s .= "\n" . 'public static $' . $property . ' = ' . "\n";
			$phplib_modules_s .= var_export($value, 1) . ';';
		}
		$phplib_modules_s.="\n" . '}';
		file_put_contents($phplib_modules, $phplib_modules_s);
	}

}