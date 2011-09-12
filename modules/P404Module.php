<?php

class P404Module extends BaseModule {

	function generateData() {
		$requested_uri = $_SERVER['REQUEST_URI'];
		$this->data['requested_uri'] = '/' . Request::$pageName . '/' . implode('/', Request::getAllParameters());
	}

}