<?php

class XMLClass {

	public static $xml; // собственно весь xml
	public static $rootNode; // рутовая нода
	public static $pageNode; // нода страницы
	public static $CurrentUserNode; // нода текущего юзера
	private static $initialized = false;

	private static function initialize() {
		if (self::$initialized)
			return self::$rootNode;
		self::$xml = new DOMDocument();
		self::$xml->loadXML("<xml version=\"1.0\" encoding=\"utf-8\" >" . "<root></root></xml>");

		self::$rootNode = self::getRootElement();
		self::$initialized = true;
	}
	
	public static function reinitialize() {
		self::$initialized = false;
		self::initialize();
	}

	private static function getRootElement() {
		$element = self::$xml->getElementsByTagName("root");
		return $element->item(0);
	}

	// добавляем в корень xml дерева ноду 
	public static function appendNode($xmlNode, $nodeName = false) {
		self::initialize();
		if ($nodeName)
			$xmlNode->setAttribute('name', $nodeName);
		self::$rootNode->appendChild($xmlNode);
		return $xmlNode;
	}

	// выставляем свойства ноде
	public static function setNodeProps($xmlNode, array $properties) {
		foreach ($properties as $f => $v)
			if (is_string($v))
				$xmlNode->setAttribute($f, $v);
	}

	public static function createNodeFromObject($data, $parent = false, $nodeName = 'data', $recursive = true) {
		self::initialize();
		$ret = self::_createNodeFromObject($data, $parent, $nodeName, $recursive);
		return $ret;
	}

	// превращаем массив/объект/строку в ноду, с вложениями, рекурсивно
	public static function _createNodeFromObject($data, $parent = false, $nodeName = 'data', $recursive = true , $xml = false) {
		$xml = $xml?$xml:self::$xml;
		if (!is_array($data))
			return;
		$nodeName = self::prepareNodeName($nodeName, 'item');
		if (!$parent)
			$parent = $xml->createElement($nodeName);


		if (is_array($data) || is_object($data)) {
			foreach ($data as $field => $value) {
				if (is_array($value) || is_object($value)) {
					if ($recursive) {
						$recvNode = self::_createNodeFromObject($value, false, $field);
						if ($recvNode)
							$parent->appendChild($recvNode);
					}
				} else {
					$field = self::prepareNodeName($field);
					$parent->setAttribute($field, $value);
				}
			}
		}
	
		return $parent;
	}

	private static function prepareNodeName($name, $default = 'item') {
		if (is_numeric($name)) {
			return $default;
		}
		if (is_object($name) || is_array($name))
			return 'Object';
		return $name;
	}

	public static function dumpToBrowser() {
		self::initialize();
		header('Content-type: text/xml');
		return self::$xml->saveXML();
	}

}