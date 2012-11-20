<?php
namespace wcf\system\template;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\util\DirectoryUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * TemplateEngine loads and displays template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class TemplateEngine extends SingletonFactory {
	/**
	 * list of abbreviations to package id
	 * @var	array<integer>
	 */
	public $abbreviations = array();
	
	/**
	 * directory used to cache previously compiled templates
	 * @var	string
	 */
	public $compileDir = '';
	
	/**
	 * active language id used to identify specific language versions of compiled templates
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * directories used as template source
	 * @var	array<string>
	 */
	public $templatePaths = array();
	
	/**
	 * namespace containing template modifiers and plugins
	 * @var	string
	 */
	public $pluginNamespace = '';
	
	/**
	 * active template compiler
	 * @var	wcf\system\template\TemplateCompiler
	 */
	protected $compilerObj = null;
	
	/**
	 * forces the template engine to recompile all included templates
	 * @var	boolean
	 */
	protected $forceCompile = false;
	
	/**
	 * list of registered prefilters
	 * @var	array<string>
	 */
	protected $prefilters = array();
	
	/**
	 * cached list of known template groups
	 * @var	array
	 */
	protected $templateGroupCache = array();
	
	/**
	 * active template group id
	 * @var	integer
	 */
	protected $templateGroupID = 0;
	
	/**
	 * all available template variables and those assigned during runtime
	 * @var	array<array>
	 */
	protected $v = array();
	
	/**
	 * all cached variables for usage after execution in sandbox
	 * @var	array
	 */
	protected $sandboxVars = null;
	
	/**
	 * contains all templates with assigned template listeners.
	 * @var	array<array>
	 */
	protected $templateListeners = array();
	
	/**
	 * current environment
	 * @var	string
	 */
	protected $environment = 'user';
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->abbreviations['wcf'] = 1;
		$this->templatePaths = array(1 => WCF_DIR.'templates/');
		$this->pluginNamespace = 'wcf\system\template\plugin\\';
		$this->compileDir = WCF_DIR.'templates/compiled/';
		
		$this->loadTemplateGroupCache();
		$this->assignSystemVariables();
		$this->loadTemplateListeners();
	}
	
	/**
	 * Adds a new application.
	 * 
	 * @param	string		$abbreviation
	 * @param	integer		$packageID
	 * @param	string		$templatePath
	 */
	public function addApplication($abbreviation, $packageID, $templatePath) {
		$this->abbreviations[$abbreviation] = $packageID;
		$this->templatePaths[$packageID] = $templatePath;
	}
	
	/**
	 * Sets active language id.
	 * 
	 * @param	integer		$languageID
	 */
	public function setLanguageID($languageID) {
		$this->languageID = $languageID;
	}
	
	/**
	 * Assigns some system variables.
	 */
	protected function assignSystemVariables() {
		$this->v['tpl'] = array();
		
		// assign super globals
		$this->v['tpl']['get'] =& $_GET;
		$this->v['tpl']['post'] =& $_POST;
		$this->v['tpl']['cookie'] =& $_COOKIE;
		$this->v['tpl']['server'] =& $_SERVER;
		$this->v['tpl']['env'] =& $_ENV;
		
		// system info
		$this->v['tpl']['now'] = TIME_NOW;
		$this->v['tpl']['template'] = '';
		$this->v['tpl']['includedTemplates'] = array();
		
		// section / foreach / capture arrays
		$this->v['tpl']['section'] = $this->v['tpl']['foreach'] = $this->v['tpl']['capture'] = array();
	}
	
	/**
	 * Assigns a template variable.
	 * 
	 * @param	mixed		$variable
	 * @param	mixed		$value
	 */
	public function assign($variable, $value = '') {
		if (is_array($variable)) {
			foreach ($variable as $key => $value) {
				if (empty($key)) continue;
				
				$this->assign($key, $value);
			}
		}
		else {
			$this->v[$variable] = $value;
		}
	}
	
	/**
	 * Appends content to an existing template variable.
	 * 
	 * @param 	mixed 		$variable
	 * @param 	mixed 		$value
	 */
	public function append($variable, $value = '') {
		if (is_array($variable)) {
			foreach ($variable as $key => $val) {
				if ($key != '') {
					$this->append($key, $val);
				}
			}
		}
		else {
			if (!empty($variable)) {
				if (isset($this->v[$variable])) {
					if (is_array($this->v[$variable]) && is_array($value)) {
						$keys = array_keys($value);
						foreach ($keys as $key) {
							if (isset($this->v[$variable][$key])) {
								$this->v[$variable][$key] .= $value[$key];
							}
							else { 
								$this->v[$variable][$key] = $value[$key];
							}
						}
					}
					else {
						$this->v[$variable] .= $value;
					}
				}
				else {
					$this->v[$variable] = $value;
				}
			}
		}
	}
	
	/**
	 * Prepends content to an existing template variable.
	 * 
	 * @param 	mixed 		$variable
	 * @param 	mixed 		$value
	 */
	public function prepend($variable, $value = '') {
		if (is_array($variable)) {
			foreach ($variable as $key => $val) {
				if ($key != '') {
					$this->prepend($key, $val);
				}
			}
		}
		else {
			if (!empty($variable)) {
				if (isset($this->v[$variable])) {
					if (is_array($this->v[$variable]) && is_array($value)) {
						$keys = array_keys($value);
						foreach ($keys as $key) {
							if (isset($this->v[$variable][$key])) {
								$this->v[$variable][$key] = $value[$key] . $this->v[$variable][$key];
							}
							else { 
								$this->v[$variable][$key] = $value[$key];
							}
						}
					}
					else {
						$this->v[$variable] = $value . $this->v[$variable];
					}
				}
				else {
					$this->v[$variable] = $value;
				}
			}
		}
	}
	
	/**
	 * Assigns a template variable by reference.
	 * 
	 * @param 	string 		$variable
	 * @param	mixed 		$value
	 */
	public function assignByRef($variable, &$value) {
		if (!empty($variable)) {
			$this->v[$variable] = &$value;
		}
	}
	
	/**
	 * Clears an assignment of template variables.
	 * 
	 * @param 	mixed 		$variables
	 */
	public function clearAssign(array $variables) {
		foreach ($variables as $key) {
			unset($this->v[$key]);
		}
	}
	
	/**
	 * Clears assignment of all template variables. This should not be called
	 * during runtime as it could leed to an unexpected behaviour.
	 */
	public function clearAllAssign() {
		$this->v = array();
	}
	
	/**
	 * Outputs a template.
	 * 
	 * @param	string		$templateName
	 * @param	string		$application
	 * @param	boolean		$sendHeaders
	 */
	public function display($templateName, $application = 'wcf', $sendHeaders = true) {
		if ($sendHeaders) {
			HeaderUtil::sendHeaders();
			
			// call shouldDisplay event
			if (!defined('NO_IMPORTS')) EventHandler::getInstance()->fireAction($this, 'shouldDisplay');
		}
		
		$tplPackageID = $this->getPackageID($templateName, $application);
		$compiledFilename = $this->getCompiledFilename($templateName);
		$sourceFilename = $this->getSourceFilename($templateName, $tplPackageID);
		$metaDataFilename = $this->getMetaDataFilename($templateName);
		$metaData = $this->getMetaData($templateName, $metaDataFilename);
		
		// check if compilation is necessary
		if (($metaData === null) || !$this->isCompiled($templateName, $sourceFilename, $compiledFilename, $application, $metaData)) {
			// compile
			$this->compileTemplate($templateName, $sourceFilename, $compiledFilename, array(
				'application' => $application,
				'data' => $metaData,
				'filename' => $metaDataFilename
			));
		}
		
		// assign current package id
		$this->assign('__APPLICATION', $application);
		
		include($compiledFilename);
		
		if ($sendHeaders) {
			// call didDisplay event
			if (!defined('NO_IMPORTS')) EventHandler::getInstance()->fireAction($this, 'didDisplay');
		}
	}
	
	/**
	 * Returns path and corresponding file path.
	 * 
	 * @param	string		$templateName
	 * @param	string		$application
	 * @return	integer
	 */
	public function getPackageID($templateName, $application = 'wcf') {
		$packageID = (isset($this->abbreviations[$application])) ? $this->abbreviations[$application] : null;
		if ($packageID === null) {
			throw new SystemException("Unable to find application for abbreviation '".$application."'");
		}
		
		// search within application
		if ($packageID != 1 && isset($this->templatePaths[$packageID])) {
			$path = $this->getPath($this->templatePaths[$packageID], $templateName);
			
			if (!empty($path)) {
				return $packageID;
			}
		}
		
		// search within WCF
		$path = $this->getPath($this->templatePaths[1], $templateName);
		if (!empty($path)) {
			return 1;
		}
		
		
		throw new SystemException("Unable to find template '$templateName'");
	}
	
	/**
	 * Returns the absolute filename of a template source.
	 * 
	 * @param	string		$templateName
	 * @param	integer		$packageID
	 * @return	string		$path
	 */
	public function getSourceFilename($templateName, $packageID) {
		return $this->getPath($this->templatePaths[$packageID], $templateName);
	}
	
	/**
	 * Returns path if template was found.
	 * 
	 * @param	string		$templatePath
	 * @param	string		$templateName
	 * @return	string
	 */
	protected function getPath($templatePath, $templateName) {
		$templateGroupID = $this->templateGroupID;
		
		while ($templateGroupID != 0) {
			$templateGroup = $this->templateGroupCache[$templateGroupID];
			
			$path = $templatePath.$templateGroup->templateGroupFolderName.$templateName.'.tpl';
			if (file_exists($path)) {
				return $path;
			}
			
			$templateGroupID = $templateGroup->parentTemplateGroupID;
		}
		
		// use default template
		$path = $templatePath.$templateName.'.tpl';
		
		if (file_exists($path)) {
			return $path;
		}
		
		return '';
	}
	
	/**
	 * Returns the absolute filename of a compiled template.
	 * 
	 * @param 	string 		$templateName
	 */
	public function getCompiledFilename($templateName) {
		return $this->compileDir.PACKAGE_ID.'_'.$this->templateGroupID.'_'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * Returns the absolute filename for template's meta data.
	 * 
	 * @param	string		$templateName
	 */
	public function getMetaDataFilename($templateName) {
		return $this->compileDir.PACKAGE_ID.'_'.$this->templateGroupID.'_'.$templateName.'.meta.php';
	}
	
	/**
	 * Checks wheater a template is already compiled or not.
	 * 
	 * @param	string		$templateName
	 * @param 	string 		$sourceFilename
	 * @param 	string 		$compiledFilename
	 * @param	string		$application
	 * @param	array		$metaData
	 * @return 	boolean 	$isCompiled
	 */
	protected function isCompiled($templateName, $sourceFilename, $compiledFilename, $application, array $metaData) {
		if ($this->forceCompile || !file_exists($compiledFilename)) {
			return false;
		}
		else {
			$sourceMTime = @filemtime($sourceFilename);
			$compileMTime = @filemtime($compiledFilename);
			
			if ($sourceMTime >= $compileMTime) {
				return false;
			}
			else {
				// check for meta data
				if (!empty($metaData['include'])) {
					foreach ($metaData['include'] as $includedTemplate) {
						$tplPackageID = $this->getPackageID($includedTemplate, $application);
						$includedTemplateFilename = $this->getSourceFilename($includedTemplate, $tplPackageID);
						$includedMTime = @filemtime($includedTemplateFilename);
						
						if ($includedMTime >= $compileMTime) {
							return false;
						}
					}
				}
				
				// check for template listeners
				if ($this->hasTemplateListeners($templateName)) {
					$this->loadTemplateListenerCode($templateName);
					
					$templateListenerCache = WCF_DIR.'cache/templateListener/'.PACKAGE_ID.'-'.$this->environment.'-'.$templateName.'.php';
					$templateListenerCacheMTime = @filemtime($templateListenerCache);
					
					return !($sourceMTime >= $templateListenerCacheMTime);
				}
				
				return true;
			}
		}
	}
	
	/**
	 * Compiles a template.
	 * 
	 * @param 	string 		$templateName
	 * @param 	string 		$sourceFilename
	 * @param 	string 		$compiledFilename
	 * @param	array		$metaData
	 */
	protected function compileTemplate($templateName, $sourceFilename, $compiledFilename, array $metaData) {
		// get source
		$sourceContent = $this->getSourceContent($sourceFilename);
		
		// compile template
		$this->getCompiler()->compile($templateName, $sourceContent, $compiledFilename, $metaData);
	}
	
	/**
	 * Returns a new template compiler object.
	 * 
	 * @return	wcf\system\template\TemplateCompiler
	 */
	public function getCompiler() {
		if ($this->compilerObj === null) {
			$this->compilerObj = new TemplateCompiler($this);
		}
		
		return $this->compilerObj;
	}
	
	/**
	 * Reads the content of a template file.
	 * 
	 * @param	string		$sourceFilename
	 * @return	string		$sourceContent
	 */
	public function getSourceContent($sourceFilename) {
		$sourceContent = '';
		if (!file_exists($sourceFilename) || (($sourceContent = @file_get_contents($sourceFilename)) === false)) {
			throw new SystemException("Could not open template '$sourceFilename' for reading");
		}
		else {
			return $sourceContent;
		}
	}
	
	/**
	 * Returns the class name of a plugin.
	 * 
	 * @param	string		$type
	 * @param	string		$tag
	 * @return	string
	 */
	public function getPluginClassName($type, $tag) {
		return $this->pluginNamespace.StringUtil::firstCharToUpperCase($tag).StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($type)).'TemplatePlugin';
	}
	
	/**
	 * Enables execution in sandbox.
	 */
	public function enableSandbox() {
		if ($this->sandboxVars === null) {
			$this->sandboxVars = $this->v;
		}
		else {
			throw new SystemException('TemplateEngine is already in sandbox mode. Disable the current sandbox mode before you enable a new one.');
		}
	}
	
	/**
	 * Disables execution in sandbox.
	 */
	public function disableSandbox() {
		if ($this->sandboxVars !== null) {
			$this->v = $this->sandboxVars;
			$this->sandboxVars = null;
		}
		else {
			throw new SystemException('TemplateEngine is not in sandbox mode at the moment.');
		}
	}
	
	/**
	 * Returns the output of a template.
	 * 
	 * @param	string		$templateName
	 * @param	string		$application
	 * @param	array		$variables
	 * @param	boolean		$sandbox	enables execution in sandbox
	 * @return	string
	 */
	public function fetch($templateName, $application = 'wcf', array $variables = array(), $sandbox = false) {
		// enable sandbox
		if ($sandbox) {
			$this->enableSandbox();
		}
		
		// add new template variables
		if (!empty($variables)) {
			$this->v = array_merge($this->v, $variables);
		}
		
		// get output
		ob_start();
		$this->display($templateName, $application, false);
		$output = ob_get_contents();
		ob_end_clean();
		
		// disable sandbox
		if ($sandbox) {
			$this->disableSandbox();
		}
		
		return $output;
	}
	
	/**
	 * Executes a compiled template scripting source and returns the result.
	 *
	 * @param	string		$compiledSource
	 * @param	array		$variables
	 * @param	boolean		$sandbox	enables execution in sandbox
	 * @return	string
	 */
	public function fetchString($compiledSource, array $variables = array(), $sandbox = true) {
		// enable sandbox
		if ($sandbox) {
			$this->enableSandbox();
		}
		
		// add new template variables
		if (!empty($variables)) {
			$this->v = array_merge($this->v, $variables);
		}
		
		// get output
		ob_start();
		eval('?>'.$compiledSource);
		$output = ob_get_contents();
		ob_end_clean();
		
		// disable sandbox
		if ($sandbox) {
			$this->disableSandbox();
		}
		
		return $output;
	}
	
	/**
	 * Deletes all compiled templates.
	 * 
	 * @param 	string		$compileDir
	 */
	public static function deleteCompiledTemplates($compileDir = '') {
		if (empty($compileDir)) $compileDir = WCF_DIR.'templates/compiled/';
		
		// delete compiled templates
		DirectoryUtil::getInstance($compileDir)->removePattern(new Regex('.*_.*_.*\.php$'));
	}
	
	/**
	 * Returns an array with all prefilters.
	 * 
	 * @return 	array<string>
	 */
	public function getPrefilters() {
		return $this->prefilters;
	}
	
	/**
	 * Returns the active template group id.
	 * 
	 * @return	integer
	 */
	public function getTemplateGroupID() {
		return $this->templateGroupID;
	}
	
	/**
	 * Sets the active template group id.
	 * 
	 * @param	integer		$templateGroupID
	 */
	public function setTemplateGroupID($templateGroupID) {
		if ($templateGroupID && !isset($this->templateGroupCache[$templateGroupID])) {
			$templateGroupID = 0;
		}
		
		$this->templateGroupID = $templateGroupID;
	}
	
	/**
	 * Loads cached template group information.
	 */
	protected function loadTemplateGroupCache() {
		CacheHandler::getInstance()->addResource(
			'templateGroups',
			WCF_DIR.'cache/cache.templateGroups.php',
			'wcf\system\cache\builder\TemplateGroupCacheBuilder'
		);
		$this->templateGroupCache = CacheHandler::getInstance()->get('templateGroups');
	}
	
	/**
	 * Registers prefilters.
	 * 
	 * @param	array<string>		$prefilters
	 */
	public function registerPrefilter(array $prefilters) {
		foreach ($prefilters as $name) {
			$this->prefilters[$name] = $name;
		}
	}
	
	/**
	 * Sets the dir for the compiled templates.
	 *
	 * @param	string		$compileDir
	 */
	public function setCompileDir($compileDir) {
		if (!is_dir($compileDir)) {
			throw new SystemException("'".$compileDir."' is not a valid dir");
		}
		
		$this->compileDir = $compileDir;
	}
	
	/**
	 * Includes a template.
	 * 
	 * @param	string		$templateName
	 * @param	string		$application
	 * @param	array		$variables
	 * @param	boolean		$sandbox	enables execution in sandbox
	 */
	protected function includeTemplate($templateName, $application, array $variables = array(), $sandbox = true) {
		// enable sandbox
		if ($sandbox) {
			$this->enableSandbox();
		}
		
		// add new template variables
		if (!empty($variables)) {
			$this->v = array_merge($this->v, $variables);
		}
		
		// display template
		$this->display($templateName, $application, false);
		
		// disable sandbox
		if ($sandbox) {
			$this->disableSandbox();
		}
	}
	
	/**
	 * Returns the value of a template variable.
	 * 
	 * @param	string		$varname
	 * @return	mixed
	 */
	public function get($varname) {
		if (isset($this->v[$varname])) {
			return $this->v[$varname];
		}
		
		return null;
	}
	
	/**
	 * Loads all available template listeners.
	 */
	protected function loadTemplateListeners() {
		$cacheName = 'templateListener-'.PACKAGE_ID.'-'.$this->environment;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\TemplateListenerCacheBuilder'
		);
		
		$this->templateListeners = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns true if requested template has assigned template listeners.
	 * 
	 * @param	string		$templateName
	 * @param	string		$application
	 * @return	boolean
	 */
	public function hasTemplateListeners($templateName, $application = 'wcf') {
		if (isset($this->templateListeners[$application]) && isset($this->templateListeners[$application][$templateName])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Loads template code for specified template.
	 * 
	 * @param	string		$templateName
	 */
	protected function loadTemplateListenerCode($templateName) {
		// cache was already loaded
		if (!isset($this->templateListeners[$templateName]) || !empty($this->templateListeners[$templateName])) return;
		
		$cacheName = PACKAGE_ID.'-'.$this->environment.'-'.$templateName;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/templateListener/'.$cacheName.'.php',
			'wcf\system\cache\builder\TemplateListenerCodeCacheBuilder'
		);
		
		$this->templateListeners[$templateName] = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns template listener's code.
	 * 
	 * @param	string		$templateName
	 * @param	string		$eventName
	 * @return	string
	 */
	public function getTemplateListenerCode($templateName, $eventName) {
		$this->loadTemplateListenerCode($templateName);
		
		if (isset($this->templateListeners[$templateName][$eventName])) {
			return implode("\n", $this->templateListeners[$templateName][$eventName]);
		}
		
		return '';
	}
	
	/**
	 * Reads meta data from file.
	 * 
	 * @param	string		$templateName
	 * @param	string		$filename
	 * @return	array
	 */
	protected function getMetaData($templateName, $filename) {
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}
		
		// get file contents
		$contents = file_get_contents($filename);
		
		// find first newline
		$position = strpos($contents, "\n");
		if ($position === false) {
			return null;
		}
		
		// cut contents
		$contents = substr($contents, $position + 1);
		
		// read serializes data
		$data = @unserialize($contents);
		if ($data === false || !is_array($data)) {
			return null;
		}
		
		return $data;
	}
}
