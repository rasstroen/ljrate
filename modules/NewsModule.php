<?php

class NewsModule extends BaseModule {

	function generateData() {
		$this->data = array(
		    'news' => array(
			array(
			    'title' => 'first',
			    'url' => '/a',
			),
			array(
			    'title' => 'second',
			    'url' => '/b',
			),
		    ),);
	}

}