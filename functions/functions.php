<pre><?php

function valid_email_address($mail) {
	$user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
	$domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
	$ipv4 = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
	$ipv6 = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';
	return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $mail);
}

function isLjUrl($url) {
	if (strpos($url, 'livejournal') !== false) {
		return true;
	}
	return false;
}

function getAuthorAndIdByUrl($url) {
	$id = 0;
	$author = '';
	$urlp = explode('/', $url);
	$id = $urlp[sizeof($urlp) - 1];
	$idp = explode('.', $id);
	$id = isset($idp[0]) ? (int) $idp[0] : 0;
	if (isset($urlp[2])) {
		$sub = explode('.', $urlp[2]);
		if (isset($sub[0])) {
			$author = $sub[0];
		}
	}
	$author = str_replace('_', '-', $author);
	return array($author, $id);
}

function curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 2);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, '1.1');
	curl_setopt($ch, CURLOPT_USERAGENT, 'Ljrate.ru');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    "Content-Type: application/x-www-form-urlencoded",
	    "Accept : text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
	    "Accept-Language : ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3",
	    "Accept-Charset : UTF-8,*",
	    "Keep-Alive : 115",
	));
	ob_start();
	$result = curl_exec($ch);
	return $result;
}
