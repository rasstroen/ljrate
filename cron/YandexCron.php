<?php

require 'Cron.php';

class YandexCron extends Cron {

	private $url = 'http://blogs.yandex.ru/entriesapi?p=';

	function work() {
		// забираем от яндекса кусок
		$i = 0;
		while (is_array($data = $this->getYandexPage($i++))) {
			
		}
	}

	function getYandexPage($page_id = 1) {
		$url = $this->url . (int) $page_id;
		$xml = new DOMDocument;
		$xml->loadXML(curl($url));
		$k = 0;
		$out = false;
		/* @var $xml DomDocument */
		foreach ($xml->getElementsByTagName('item') as $postNode) {
			$k++;
			$j = 0;
			/* @var $postNode DOMElement */
			$nodeItems = $postNode->getElementsByTagName('*');
			/* @var $nodeItems DOMNodeList */
			while ($nodeItems->item($j++)) {
				if (is_a($nodeItems->item($j), 'DOMElement'))
					$out[$k][$nodeItems->item($j)->nodeName] = $nodeItems->item($j)->nodeValue;
			}
		}
		return $out;
	}

}

new YandexCron();