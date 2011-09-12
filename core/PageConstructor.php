<?php

/* этот класс пинает всех:
 * собирает xslt шаблоны
 * собирает xml дерево из деревьев модулей
 * собирает xslt шаблон из кусков
 * выполняет трансормацию
 * возвращает готовый HTML
 */

class PageConstructor {

	private $pageName;
	private $pageSettings;
	private $xsltFiles = array();

	function __construct($pageName) {
		$this->pageName = $pageName;
		$this->pageSettings = isset(LibPages::$pages[$this->pageName]) ? LibPages::$pages[$this->pageName] : false;
		if (!$this->pageSettings)
			throw new Exception('No page params in lib for #' . $pageName, Error::E_MODULE_SETTINGS_NOT_FOUND);
		if (!isset($this->pageSettings['xslt']))
			throw new Exception('No page param [xslt] in lib for #' . $pageName, Error::E_MODULE_SETTINGS_NOT_FOUND);
	}

	private function processModule($moduleName, $additionalSettings = array(), $inherited = false) {
		// запускаем модуль
		if (isset(LibModules::$modules[$moduleName]))
			eval('$module = new ' . $moduleName . '($moduleName, $additionalSettings);');
		else
			throw new Exception('module ' . $moduleName . ' missed in modules library', Error::E_MODULE_NOT_FOUND);
		/* @var $module BaseModule */
		// получаем xml от модуля
		Log::timing($moduleName . ' : processModule');
		$module->process();
		Log::timing($moduleName . ' : processModule');
		$xmlNode = $module->getResultXML();
		if ($inherited)
			$xmlNode->setAttribute('inherited', 1);
		else
			$xmlNode->setAttribute('inherited', 0);

		// добавляем xsl файл в список
		$xsltFileName = $module->getXSLTFileName();

		if ($xsltFileName)
			$this->addXsltFile($moduleName, $xsltFileName, $inherited);
		else if ($xsltFileName == null)
			$this->addXsltNullFile($moduleName);

		if ($xmlNode !== false) {
			XMLClass::setNodeProps(XMLClass::appendNode($xmlNode, $moduleName), $module->getProps());
		}
	}

	public function process() {
		global $current_user;
		/* @var $current_user CurrentUser */
		XMLClass::$pageNode = XMLClass::createNodeFromObject($this->pageSettings, false, 'page', false);
		XMLClass::appendNode(XMLClass::$pageNode, $this->pageName);
		XMLClass::$pageNode->setAttribute('current_url', Request::$url);
		XMLClass::$pageNode->setAttribute('page_url', Config::need('www_path') . '/' . Request::$pageName . '/');
		XMLClass::$pageNode->setAttribute('prefix', Config::need('www_path') . '/');
		if ($current_user->authorized)
			XMLClass::$CurrentUserNode = XMLClass::createNodeFromObject($current_user->getXMLInfo(), false, 'current_user', false);
		else
			XMLClass::$CurrentUserNode = XMLClass::createNodeFromObject(array(), false, 'current_user', false);
		XMLClass::$pageNode->appendChild(XMLClass::$CurrentUserNode);
		// втыкаем модули страницы
		$role = $current_user->getRole();
		
		if (isset($this->pageSettings['modules']) && is_array($this->pageSettings['modules'])) {
			foreach ($this->pageSettings['modules'] as $moduleName => $additionalSettings) {
				if(isset($additionalSettings['roles'][$role]))
				$this->processModule($moduleName, $additionalSettings);
			}
		}
		// xml дерево создано, теперь генерируем xslt шаблон
		// выдаем html
		//Request::$responseType = 'xml';
		switch (Request::$responseType) {
			case 'xml':case 'xmlc':
				return XMLClass::dumpToBrowser();
				break;
			case 'xsl':case 'xslc':
				$xslTemplateClass = new XSLClass($this->pageSettings['xslt']);
				$xslTemplateClass->setTemplates($this->xsltFiles);
				return $xslTemplateClass->dumpToBrowser();
				break;
			case 'html':
				$xslTemplateClass = new XSLClass($this->pageSettings['xslt']);
				$xslTemplateClass->setTemplates($this->xsltFiles);
				$html = $xslTemplateClass->getHTML(XMLClass::$xml);
				if ($xslTemplateClass->fetched_from_cache) {
					// чтобы знать, что файл из кеша
					Log::logHtml('xslt template GOT from cache');
				}
				if ($xslTemplateClass->puted_into_cache) {
					// чтобы знать, что файл из кеша
					Log::logHtml('xslt template PUT to cache');
				}

				return $html;
				break;
			default:
				return XMLClass::dumpToBrowser();
				break;
		}
	}

	//-----------
	// добавляем шаблон модуля в список шаблонов страницы
	private function addXsltFile($moduleName, $xsltFileName) {
		if (!isset($this->xsltFiles[$moduleName]))
			$this->xsltFiles[$moduleName] = $xsltFileName;
	}

	private function addXsltNullFile($moduleName) {
		$this->xsltFiles[$moduleName] = 'null';
	}

}