<?php

// проверяем настройки модуля,
// если кеш включен

class BaseModule {

	public $xmlPart = false;
	protected $moduleName = '';
	protected $settings = array();
	protected $xml_cache_name = '';
	protected $data = array(); // выходные данные модуля
	protected $cached = false;
	protected $cache_enabled = false;
	protected $cachetype = false;
	protected $current_view = false; // вьюшка. имямодуля_вьюшка.xsl
	protected $props;
	protected $writeParameters = array();
	private $xHTMLCachingEnabled = false; // модуль можно в виде xHTML положить в кеш

	function __construct($moduleName, array $additionalSettings) {
		global $dev_mode;
		$this->moduleName = $moduleName;
		foreach (LibModules::$modules[$moduleName]['params'] as $settingName => $value) {
			$this->settings[$settingName] = $value;
		}
		foreach ($additionalSettings as $settingName => $value) {
			// именно на этой странице у модуля появились дополнительные настройки
			$this->settings[$settingName] = $value;
		}
		$this->props = LibModules::$modules[$moduleName];
		// вьюшка по умолчанию - первая из списка вьюшек
		if (isset($this->props['views'])) {
			$this->setCurrentView(array_shift($this->props['views']));
		}
		$this->props['block'] = $this->settings['block'];
		unset($this->settings['block']);
		

		// цепляем данные из соответствующего модуля записи
		$this->writeParameters = PostWrite::getWriteParameters($moduleName);
		foreach ($this->writeParameters as $f => $v) {
			$this->data[$f] = $v;
		}
		if (isset($this->writeParameters['cache']) && $this->writeParameters['cache'] == false) {
			$this->dropCache();
		}

		if (count($this->writeParameters)) {
			$this->disableCaching();
			Log::logHtml('caching for module # ' . $moduleName . ' disabled [post params]');
		} else
		// будем ли использовать кэш для хранения xml результата работы скрипта?
		if ($this->checkCacheSettings()) {
			// вынимаем из кеша
			$cachedXml = $this->getFromCache();
			// если получилось
			if ($cachedXml) {
				Log::logHtml('caching for module # ' . $moduleName . ' enabled [got xml from cache]');
				$this->beforeCachedRun();
				$this->xmlPart = $cachedXml;
			}
		} else {
			Log::logHtml('caching for module # ' . $moduleName . ' disabled [module settings]');
		}
	}

	/**
	 * перед тем как отдать данные из кеша выполняем эту ф-цию
	 */
	protected function beforeCachedRun() {
		
	}
	
	protected function setPageTitle($title){
		XMLClass::$pageNode->setAttribute('title', $title);
	}
	
	protected function setPageDescriptionMeta($description){
		XMLClass::$pageNode->setAttribute('description', $description);
	}
	
	protected function setPageKeywordsMeta($keywords){
		XMLClass::$pageNode->setAttribute('keywords', $keywords);
	}

	protected function setCurrentView($view) {
		$this->props['current_view'] = $view;
	}

	protected function getCurrentView() {
		return $this->current_view;
	}

	protected function disableCaching() {
		$this->cache_enabled = false;
	}

	protected function dropCache() {
		$i = 0;
		$params = Request::getAllParameters();
		while ($i < count($params)) {
			$i++;
			$name = Request::$pageName . '_' . $this->moduleName . '_' . (implode('', array_slice($params,0,$i)));
			Cache::drop($name, Cache::DATA_TYPE_XML);
		}
	}

	protected function checkCacheSettings() {
		if ((isset($this->settings['cache']) && $this->settings['cache']) || (isset($this->props['params']['cache']) && $this->props['params']['cache'] )) {
			$this->xml_cache_name = Request::$pageName . '_' . $this->moduleName . '_' . (implode('', Request::getAllParameters()));
			$this->cache_enabled = true;
			if (isset($this->settings['xHTML']) && $this->settings['xHTML']) {
				if ((Request::$responseType == 'xsl') || (Request::$responseType == 'xml')) { // при просмотре xml и xslt отрубаем кеширование
					$this->cache_enabled = false;
					$this->xHTMLCachingEnabled = false;
				}else
					$this->xHTMLCachingEnabled = true;
			}
		}
		return $this->cache_enabled;
	}

	public function getProps() {
		return $this->props;
	}

	// генерируем xml дерево модуля
	public final function process() {
		if ($this->xmlPart !== false) {
			// xml уже взят
			$this->xmlPart->setAttribute('from_cache', true);
			return true;
		}
		$this->generateData();
	}

	protected function generateData() {
		throw new Exception($this->moduleName . '->generateData() must be implemented', Error::E_MUST_BE_IMPLEMENTED);
	}

	// пытаемся получить ноду из кеша
	protected function getFromCache() {
		if (!$this->cache_enabled)
			return false;
		if (isset($this->props['params']['cache_sec']))
			$cache_sec = (int) $this->props['params']['cache_sec'];
		if (isset($this->settings['cache_sec']))
			$cache_sec = (int) $this->settings['cache_sec'];

		if ($data = Cache::get($this->xml_cache_name, Cache::DATA_TYPE_XML, $cache_sec)) {
			Log::timingplus($this->moduleName . ' : XML from cache');
			$doc = new DOMDocument;
			$doc->loadXML($data);
			// говорим нашему дереву что этот кусок из кеша будет вставлен
			$part = $doc->getElementsByTagName("module")->item(0);
			$this->xmlPart = XMLClass::$xml->importNode($part, true);
			Log::timingplus($this->moduleName . ' : XML from cache');
			return $this->xmlPart;
		}
		return false;
	}

	// отправляем ноду в кеш
	protected function putInCache() {
		if ($this->cache_enabled) {
			if (isset($this->settings['cache_sec']))
				$cache_sec = (int) $this->settings['cache_sec'];

			Cache::set($this->xml_cache_name, XMLClass::$xml->saveXML($this->xmlPart), $cache_sec, Cache::DATA_TYPE_XML);
			Log::logHtml('caching for module # ' . $this->moduleName . ' enabled [put into cache]');
		}
	}

	public function getResultXML() {
		if ($this->xmlPart !== false)
			return $this->xmlPart;
		$this->xmlPart = XMLClass::createNodeFromObject(array('data' => $this->data, 'settings' => $this->settings), false, 'module');
		if ($this->xHTMLCachingEnabled) {
			Log::timingplus('BaseModule:xHTML generating #' . $this->moduleName);
			// процессим кусок XML с шаблоном, чтобы получить xHTML
			// шаблон, который отпроцессит только наш модуль - null.xsl
			$XSLClass = new XSLClass(LibPages::$pages[Request::$pageName]['xslt'], $xHTML = true);
			$arr = array($this->moduleName => $this->getXSLTFileName(true));
			// и шаблон модуля
			$XSLClass->setTemplates($arr);
			// создаем документ
			$xml = new DOMDocument('1.0', 'UTF-8');
			$xml->loadXML("<xml version=\"1.0\" encoding=\"utf-8\" >" . "<root></root></xml>");
			$rootNode = $xml->getElementsByTagName("root")->item(0);
			// нода page также нужна для корректной обработки шаблонаы
			$pageNode = $xml->importNode(XMLClass::$pageNode);
			$rootNode->appendChild($pageNode);
			// вставляем в него ноду с данными модуля
			$xHTMLnode = $xml->importNode($this->xmlPart, true);
			$rootNode->appendChild($xHTMLnode);
			$xHTMLnode->setAttribute('name', $this->moduleName);
			// теперь полученный xml процессим с шаблоном
			$xHTML = $XSLClass->getHTML($xml);
			// полученный xHTML нужно вбить в XML ноду "module"
			$xHTMLdoc = new DOMDocument('1.0', 'UTF-8');
			$xHTMLdoc->loadHTML($xHTML);
			$xpath = new DOMXPath($xHTMLdoc);
			$part = $xpath->query('body')->item(0);

			$part = $xHTMLdoc->importNode($part, true);
			// в part лежит HTML код
			// копируем его в новую ноду
			$NewElement = XMLClass::$xml->createElement('module');
			// Clone the attributes:
			foreach ($part->attributes as $attribute)
				$NewElement->setAttribute($attribute->name, $attribute->value);

			foreach ($part->childNodes as $child)
				$NewElement->appendChild(XMLClass::$xml->importNode($child, true));

			$this->xmlPart = $NewElement;
			Log::timingplus('BaseModule:xHTML generating #' . $this->moduleName);
		}
		$this->putInCache();
		return $this->xmlPart;
	}

	// сбрасываем кеш для данного модуля
	public function dropXMLCache() {
		return false;
	}

	// отдаем имя шаблона
	public function getXSLTFileName($ignoreXHTML = false) {
		if (!$ignoreXHTML && $this->xHTMLCachingEnabled) {
			// трансформация не нужна
			return null;
		}
		return $this->getXSLTFileNameView($ignoreXHTML);
	}

	public function getXSLTFileNameView($ignoreXHTML = false) {
		if (isset($this->props['xslt'])) {
			$xsltBaseName = $this->props['xslt'];
			// какая вьюшка применяется для модуля на этой конкретной странице?
			if ($this->props['current_view'])
				$xsltBaseName.= '_' . $this->props['current_view'];
			$this->props['current_view_template'] = $xsltBaseName;
			return $xsltBaseName;
		}
		return false;
	}

}