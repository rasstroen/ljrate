<?php

class MenuModule extends BaseModule {
	
	function checkCacheSettings() {
		return false;
	}

	function generateData() {
		$this->data = array(
		    'menu' => array(
			array(
			    'title' => 'title',
			    'url' => '/a',
			),
			array(
			    'title' => 'title',
			    'url' => '/a',
			),
			array(
			    'title' => 'title',
			    'url' => '/a',
			),
			array(
			    'title' => 'title',
			    'url' => '/a',
			),
		    ),
		    'something' => 'cool',
		    'xml' => 'тру!'
		);		
		
	}
	
}