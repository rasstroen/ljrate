<?php

// класс генерирует xslt шаблон для страницы
class XSLClass {

	private $xsltFileName = '';
	private $xsltFiles = '';
	private $xsltFilePathes = array();
	private $xsl_cache_name;
	private $cache_enabled = false;
	public $fetched_from_cache = false;
	public $puted_into_cache = false;
	private $pageSettings;
	private $templateNames = array();
	private $xHTML = false;

	function __construct($xsltFileName = false, $xHTML = false) {
		global $current_user;
		$this->xHTML = $xHTML;
		if ($xsltFileName) {
			$this->pageSettings = LibPages::$pages[Request::$pageName];
			$filename = Config::need('xslt_files_path') . '/' . $current_user->getTheme() . '/' . $xsltFileName;
			if (is_readable($filename)) {
				$this->xsltFileName = $filename;
			}else
				throw new Exception($filename . ' missed', Error::E_XSLT_MAIN_TEMPLATE_FILE_MISSED);
		}
	}

	// собираем файлы шаблонов модулей
	public function setTemplates(array $files) {
		global $current_user;
		foreach ($files as $moduleName => $templateName) {
			$this->templateNames[] = $templateName;
			if ($templateName == 'null') { // не нужна трансформация для модуля
				$this->xsltFilePathes[$moduleName] = array(
				    'xHTML' => true,
				    'moduleName' => $moduleName,
				    'inherited' => LibModules::$modules[$moduleName]['inherited']
				);
				continue;
			}else
				$filename = Config::need('xslt_files_path') . '/' . $current_user->getTheme() . '/modules/' . $moduleName . '/' . $templateName . '.xsl';

			if (!is_readable($filename)) {
				throw new Exception($filename . ' missed', Error::E_XSLT_TEMPLATE_FILE_MISSED);
			}

			$this->xsltFilePathes[$moduleName] = array(
			    'filename' => $filename,
			    'moduleName' => $moduleName,
			    'inherited' => LibModules::$modules[$moduleName]['inherited']
			);
		}
		// проверяем, можно ли тянуть из кеша шаблон
		$this->checkCacheSettings();
		return true;
	}

	private function getFromCache($name = false) {
		if (!$name)
			$name = $this->xsl_cache_name;
		// если можно
		if ($this->cache_enabled) {
			if (isset($this->pageSettings['params']['cache_sec']))
				$cache_sec = $this->pageSettings['params']['cache_sec'];
			else
				$cache_sec = 0;
			return Cache::get($name, Cache::DATA_TYPE_XSL, $cache_sec);
		}else
			return false;
	}

	private function putInCache($data, $name = false) {
		if (!$name)
			$name = $this->xsl_cache_name;
		// xHTML настолько суровый, что лучше бы его закешить в любом случае
		if ($this->cache_enabled || $this->xHTML) {
			if (isset($this->pageSettings['params']['cache_sec']))
				$cache_seconds = (int) $this->pageSettings['params']['cache_sec'];
			else
				$cache_seconds = 0;
			Cache::set($name, $data, $cache_seconds, Cache::DATA_TYPE_XSL);
			$this->puted_into_cache = true;
		}
	}

	private function checkCacheSettings() {
		if ((isset($this->pageSettings['params']['cache']) && $this->pageSettings['params']['cache'])) {
			$this->xsl_cache_name = Request::$pageName . '-' . implode('!', $this->templateNames);
			$this->cache_enabled = true;
		}
		return $this->cache_enabled;
	}

	public function getHTML($xml, $xslt = false) {
		$doc = new DOMDocument();
		$xsl = new XSLTProcessor();
		$doc->loadXML($xslt ? $xslt : $this->getSplitedTemplate());
		$xsl->importStyleSheet($doc);
		// кладем в кеш xslt
		Log::timingplus('XSLTProcessor');
		$res = $xsl->transformToXML($xml);
		Log::timingplus('XSLTProcessor');

		return $res;
	}

	// для обработки модулей, генерирующих xHTML, нужен главный шаблон, в котором модуль обычно обрабатывается
	// из шаблона нам нужны только инклуды
	private function getNullMainTemplate() {
		$cachename = Request::$pageName . '_nullTemplate';
		$content = $this->getFromCache($cachename);
		if (!$content) {
			$content = file_get_contents($this->xsltFileName);
			$pos = mb_strpos($content, '<xsl:template', null, 'UTF-8');
			$content = mb_substr($content, 0, $pos, 'UTF-8');
			$content .=' <xsl:template match="/"><html><head>';
			$content .='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>';
			$content .='<body><xsl:apply-templates/></body></html></xsl:template>';
			$content .='</xsl:stylesheet>';
			$this->putInCache($content, $cachename);
		}
		return $content;
	}

	private function insertIncludesCb($data) {
		global $current_user;
		/* @var $current_user CurrentUser */
		$path = Config::need('xslt_files_path') . '/' . $current_user->getTheme() . '/';
		return self::getTemplateBody($path . $data[1], $exclude_stylesheet_declaration = true);
		//return '<xsl:include href="'.$path.$data[1].'"/>';
	}

	private function insertIncludes($subject) {
		$pattern = '/\<xsl:include href="(.+)"\s?\/\>/iUs';
		$result = preg_replace_callback($pattern, 'self::insertIncludesCb', $subject);
		return $result;
	}

	private function getTemplateBody($filename, $exclude_stylesheet_declaration = false) {
		$data = file_get_contents($filename);
		if ($data && $exclude_stylesheet_declaration) {
			$data = str_replace('<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">', '', $data);
			$data = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $data);
			$data = str_replace('</xsl:stylesheet>', '', $data);
			$data = '<!--included file:' . $filename . ' -->' . $data;
		}
		return $data;
	}

	// генерим 1 большой xslt
	private function getSplitedTemplate() {
		$raw = $this->getFromCache();
		if (!$raw) {
			if ($this->xHTML)
				$raw = $this->getNullMainTemplate();
			else
				$raw = self::getTemplateBody($this->xsltFileName);
			$raw = self::insertIncludes($raw);
			$raw = str_replace('</xsl:stylesheet>', '', $raw);
			foreach ($this->xsltFilePathes as $moduleName => $props) {
				if (isset($props['xHTML'])) {
					$tmp = '<xsl:template><xsl:copy-of select="child::*"></xsl:copy-of></xsl:template>';
				}
				else
					$tmp = self::getTemplateBody($props['filename']);
				$tmp = str_replace('<xsl:template>', '<!--' . $moduleName . '--><xsl:template match="//module[@name=\'' . $moduleName . '\']">', $tmp);
				$raw .= $tmp;
			}
			$raw .= "\n" . '</xsl:stylesheet>';
			if (!$this->xHTML)
				$this->putInCache($raw);
		}else
			$this->fetched_from_cache = true;
		return $raw;
	}

	public function dumpToBrowser() {
		header('Content-type: text/xml');
		return $this->getSplitedTemplate();
	}

}