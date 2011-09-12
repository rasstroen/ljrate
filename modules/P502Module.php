<?php

class P502Module extends BaseModule {

	function generateData() {
		global $errorString,$errorCode;
		$this->data['error'] = $errorString;
		$this->data['error_code'] = $errorCode;
		
	}

}