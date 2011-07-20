<?php
namespace wcf\system\template;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * TemplateEngine loads and displays template.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class TemplateEngine extends SingletonFactory {
	/**
	 * Directory used to cache previously compiled templates
	 * 
	 * @var	string
	 */
	public $compileDir = '';
	
	/**
	 * Active language id used to identify specific language versions of compiled templates
	 * 
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * Directories used as template source
	 * 
	 * @var	array
	 */
	public $templatePaths = array();
	
	/**
	 * Namespace containing template modifiers and plugins
	 * 
	 * @var	string
	 */
	public $pluginNamespace = '';
	
	/**
	 * Active template compiler.
	 * 
	 * @var	TemplateCompiler
	 */
	protected $compilerObj = null;
	
	/**
	 * forces the template engine to recompile all included templates
	 * 
	 * @var boolean
	 */
	protected $forceCompile = false;
	
	/**
	 * list of registered prefilters
	 * 
	 * @var	array
	 */
	protected $prefilters = array();
	
	/**
	 * Cached list of known template groups.
	 * 
	 * @var	array
	 */
	protected $templateGroupCache = array();
	
	/**
	 * Active template group id.
	 * 
	 * @var	integer
	 */
	protected $templateGroupID = 0;
	
	/**
	 * Contains all available template variables and those assigned during runtime
	 * 
	 * @var	array<array>
	 */
	protected $v = array();
	
	/**
	 * Contains all templates with assigned template listeners
	 * 
	 * @var	array<array>
	 */
	protected $templateListeners = array();
	
	/**
	 * Current environment
	 * 
	 * @var	string
	 */
	protected $environment = 'user';
	
	/**
	 * Creates a new instance of TemplateEngine.
	 * 
	 * @see	TemplateEngine::getInstance()
	 */
	protected function init() {
		$this->templatePaths = array(1 => WCF_DIR.'templates/');
		$this->pluginNamespace = 'wcf\system\template\plugin\\';
		$this->compileDir = WCF_DIR.'templates/compiled/';
		
		$this->loadTemplateGroupCache();
		$this->assignSystemVariables();
		$this->loadTemplateListeners();
	}
	
	/**
	 * Adds a new template path for given package id.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$templatePath
	 */
	public function addTemplatePath($packageID, $templatePath) {
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
	 * @param	mixed	$variable
	 * @param	mixed	$value
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
	 * @param	integer		$packageID
	 * @param	boolean		$sendHeaders
	 */
	public function display($templateName, $packageID = PACKAGE_ID, $sendHeaders = true) {
		if ($sendHeaders) {
			HeaderUtil::sendHeaders();
			
			// call shouldDisplay event
			if (!defined('NO_IMPORTS')) EventHandler::getInstance()->fireAction($this, 'shouldDisplay');
		}
		
		$tplPackageID = $this->getPackageID($templateName, $packageID);
		$compiledFilename = $this->getCompiledFilename($templateName, $tplPackageID);
		$sourceFilename = $this->getSourceFilename($templateName, $tplPackageID);
		
		// check if compilation is necessary
		if (!$this->isCompiled($templateName, $sourceFilename, $compiledFilename)) {
			// compile
			$this->compileTemplate($templateName, $sourceFilename, $compiledFilename);
		}
		
		// assign current package id
		$this->assign('__PACKAGE_ID', $packageID);
		
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
	 * @param	integer		$packageID
	 * @return	integer
	 */
	protected function getPackageID($templateName, $packageID) {
		if ($packageID != 1 && isset($this->templatePaths[$packageID])) {
			$path = $this->getPath($this->templatePaths[$packageID], $templateName);
			
			if (!empty($path)) {
				return $packageID;
			}
		}
		
		$path = $this->getPath($this->templatePaths[1], $templateName);
		if (!empty($path)) {
			return 1;
		}
		
		
		throw new SystemException("Unable to find template '$templateName'", 12005);
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
			
			$path = $templatePath.$templateGroup['templateGroupFolderName'].$templateName.'.tpl';
			if (file_exists($path)) {
				return $path;
			}
			
			$templateGroupID = $templateGroup['parentTemplateGroupID'];
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
	 * @return 	string 		$path
	 */
	public function getCompiledFilename($templateName, $packageID) {
		return $this->compileDir.$packageID.'_'.$this->templateGroupID.'_'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * Checks wheater a template is already compiled or not.
	 *
	 * @param	string		$templateName
	 * @param 	string 		$sourceFilename
	 * @param 	string 		$compiledFilename
	 * @return 	boolean 	$isCompiled
	 */
	protected function isCompiled($templateName, $sourceFilename, $compiledFilename) {
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
	 */
	protected function compileTemplate($templateName, $sourceFilename, $compiledFilename) {
		// get compiler
		if (!($this->compilerObj instanceof TemplateCompiler)) {
			$this->compilerObj = $this->getCompiler();
		}
		
		// get source
		$sourceContent = $this->getSourceContent($sourceFilename);
		
		// compile template
		$this->compilerObj->compile($templateName, $sourceContent, $compiledFilename);
	}
	
	/**
	 * Returns a new template compiler object.
	 * 
	 * @return	TemplateCompiler
	 */
	protected function getCompiler() {
		return new TemplateCompiler($this);
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
			throw new SystemException("Could not open template '$sourceFilename' for reading", 12005);
		}
		else {
			return $sourceContent;
		}
	}
	
	/**
	 * Returns the class name of a plugin.
	 *
	 * @param 	string 		$type
	 * @param 	string 		$tag
	 * @return 	string 				class name
	 */
	public function getPluginClassName($type, $tag) {
		return $this->pluginNamespace.'TemplatePlugin'.StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($type)).StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($tag));
	}
	
	/**
	 * Returns the output of a template.
	 *
	 * @param 	string 		$templateName
	 * @param	integer		$packageID
	 * @return 	string 		output
	 */
	public function fetch($templateName, $packageID = PACKAGE_ID) {
		ob_start();
		$this->display($templateName, $packageID, false);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	/**
	 * Executes a compiled template scripting source and returns the result.
	 *
	 * @param 	string 		$compiledSource
	 * @return 	string 		result
	 */
	public function fetchString($compiledSource) {
		ob_start();
		eval('?>'.$compiledSource);
		$output = ob_get_contents();
		ob_end_clean();

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
		$matches = glob($compileDir . '*_*_*.php');
		if (is_array($matches)) {
			foreach ($matches as $match) @unlink($match);
		}
	}
	
	/**
	 * Returns an array with all prefilters.
	 *
	 * @return 	array
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
		CacheHandler::getInstance()->addResource('templateGroups', WCF_DIR.'cache/cache.templateGroups.php', 'wcf\system\cache\builder\CacheBuilderTemplateGroup');
		$this->templateGroupCache = CacheHandler::getInstance()->get('templateGroups');
	}
	
	/**
	 * Registers prefilters.
	 *
	 * @param 	array 		$prefilters
	 */
	public function registerPrefilter(array $prefilters) {
		foreach ($prefilters as $name) {
			$this->prefilters[$name] = $name;
		}
	}
	
	/**
	 * Sets the dir for the compiled templates.
	 *
	 * @param 	string 		$compileDir
	 */
	public function setCompileDir($compileDir) {
		if (!is_dir($compileDir)) {
			throw new SystemException("'".$compileDir."' is not a valid dir", 11014);
		}
		
		$this->compileDir = $compileDir;
	}
	
	/**
	 * Includes a template.
	 *
	 * @param 	string 		$templateName
	 * @param 	array 		$variables
	 * @param 	boolean		$sandbox	enables execution in sandbox
	 */
	protected function includeTemplate($templateName, array $variables = array(), $sandbox = true, $packageID = PACKAGE_ID) {
		// add new template variables
		if ($sandbox) {
			$templateVars = $this->v;
		}

		if (count($variables)) {
			$this->v = array_merge($this->v, $variables);
		}
		
		$this->display($templateName, $packageID, false);
		
		if ($sandbox) {
			$this->v = $templateVars;
		}
	}
	
	/**
	 * Returns the value of a template variable.
	 * 
	 * @param 	string		$varname
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
			'wcf\system\cache\builder\CacheBuilderTemplateListener'
		);
		
		$this->templateListeners = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Returns true if requested template has assigned template listeners.
	 * 
	 * @param	string		$templateName
	 * @return	boolean
	 */
	public function hasTemplateListeners($templateName) {
		if (isset($this->templateListeners[$templateName])) {
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
		if (count($this->templateListeners[$templateName])) return;
		
		$cacheName = PACKAGE_ID.'-'.$this->environment.'-'.$templateName;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/templateListener/'.$cacheName.'.php',
			'wcf\system\cache\builder\CacheBuilderTemplateListenerCode'
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
		if (isset($this->templateListeners[$templateName][$eventName])) {
			return implode("\n", $this->templateListeners[$templateName][$eventName]);
		}
		
		return '';
	}
}
