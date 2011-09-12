<?php

require 'Cron.php';

class YandexCron extends Cron {

	private $url = 'http://blogs.yandex.ru/entriesapi?p=';

	function work() {
		// забираем от яндекса кусок
		$i = 0;
		while (is_array($data = $this->getYandexPage($i++))) {
			$posts = array();
			foreach ($data as $postData) {
				if (isLjUrl($postData['link'])) {
					list($author, $id) = getAuthorAndIdByUrl($postData['link']);
					if ($id && $author) {
						$posts[$author][$id] = $postData;
					}
				}
			}
			$usernames = array_keys($posts);
			// тянем по именам пользователей
			$query = 'SELECT `id`,`name` FROM `journals` WHERE `name` IN (\'' . implode('\',\'', $usernames) . '\')';
			$journals = Database::sql2array($query, 'name');
			$toFetchJournal = array();
			$toInsert = array();
			// если нет такого пользователя - придется создать и распарсить его данные потом
			foreach ($posts as $username => $posts) {
				if (!isset($journals[$username])) {
					$toFetchJournal[$username] = $username;
				} else {
					foreach ($posts as $id => $postOne) {
						$postOne['id'] = $id;
						$postOne['author'] = $username;
						$postOne['journalId'] = $journals[$username]['id'];
						$toInsert[] = $postOne;
					}
				}
			}
			if (count($toFetchJournal)) {
				$this->addJournals($toFetchJournal);
			}
			if (count($toInsert)) {
				$this->updatePosts($toInsert);
			}
		}
	}

	function preparePostField($field, $value) {
		$maxfield = false;
		switch ($field) {
			case 'description':
			case 'link':
			case 'yablogs:ppb_username':
			case 'author':
				$field = false;
				break;
			case 'pubDate':
				$value = (int) strtotime($value);
				break;
			case 'title':
				$value = trim(strip_tags(html_entity_decode($value)));
				break;
			case 'yablogs:commenters':
			case 'yablogs:commenters24':
			case 'yablogs:comments':
			case 'yablogs:comments24':
			case 'yablogs:links':
			case 'yablogs:links24':
			case 'yablogs:linksweight':
			case 'yablogs:links24weight':
			case 'yablogs:visits24':
				$value = floor($value * 100) / 100;
				$field = explode(':', $field);
				$field = $field[1];
				$maxfield = 'max_' . $field;
				break;
			case 'journalId':
			case 'id':
				break;

			default:die($field);
		}
		return array($field, $value, $maxfield);
	}

	function updatePosts($posts) {
		foreach ($posts as $post) {
			$sqlpart = array();
			$sqlpartm = array();
			foreach ($post as $field => $value) {
				list($field, $value, $maxfield) = $this->preparePostField($field, $value);
				if ($field) {
					if ($maxfield) {
						$sqlpartm[] = '`' . $field . '`=\'' . mysql_escape_string($value) . '\'';
						$sqlpartm[] = '`' . $maxfield . '`=GREATEST(`' . $maxfield . '`,`' . $field . '`)';
						$sqlpart[] = '`' . $field . '`=\'' . mysql_escape_string($value) . '\'';
						$sqlpart[] = '`' . $maxfield . '`=\'' . mysql_escape_string($value) . '\'';
					} else {
						$sqlpart[] = '`' . $field . '`=\'' . mysql_escape_string($value) . '\'';
						$sqlpartm[] = '`' . $field . '`=\'' . mysql_escape_string($value) . '\'';
					}
				}
			}
			$values = implode(',', $sqlpart);
			$valuesm = implode(',', $sqlpartm);
			$query = 'INSERT IGNORE INTO `posts` SET ' . $values . ' ON DUPLICATE KEY UPDATE ' . $valuesm . '';
			Database::query($query);
		}
	}

	function addJournals($usernames) {
		$query = 'INSERT IGNORE INTO `journals` (`name`)  VALUES(\'' . implode('\'),(\'', $usernames) . '\')';
		Database::query($query);
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