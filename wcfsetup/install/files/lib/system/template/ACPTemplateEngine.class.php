<?php
namespace wcf\system\template;
use wcf\system\application\ApplicationHandler;

/**
 * Loads and displays template in the ACP.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class ACPTemplateEngine extends TemplateEngine {
	/**
	 * @see	\wcf\system\template\TemplateEngine::$environment
	 */
	protected $environment = 'admin';
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::__construct()
	 */
	protected function init() {
		parent::init();
		
		$this->templatePaths = array('wcf' => WCF_DIR.'acp/templates/');
		$this->compileDir = WCF_DIR.'acp/templates/compiled/';
		
		if (!defined('NO_IMPORTS')) {
			$this->loadTemplateListeners();
		}
	}
	
	/**
	 * Deletes all compiled acp templates.
	 * 
	 * @param	string		$compileDir
	 */
	public static function deleteCompiledACPTemplates($compileDir = '') {
		if (empty($compileDir)) $compileDir = WCF_DIR.'acp/templates/compiled/';
		
		self::deleteCompiledTemplates($compileDir);
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getCompiledFilename()
	 */
	public function getCompiledFilename($templateName, $application) {
		$abbreviation = 'wcf';
		if (PACKAGE_ID) {
			$abbreviation = ApplicationHandler::getInstance()->getActiveApplication()->getAbbreviation();
		}
		
		return $this->compileDir.$this->templateGroupID.'_'.$abbreviation.'_'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::setTemplateGroupID()
	 */
	public final function setTemplateGroupID($templateGroupID) {
		// template groups are not supported by the acp template engine
		$this->templateGroupID = 0;
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getTemplateListenerCode()
	 */
	public function getTemplateListenerCode($templateName, $eventName) {
		// skip template listeners within WCFSetup
		if (!PACKAGE_ID) {
			return '';
		}
		
		return parent::getTemplateListenerCode($templateName, $eventName);
	}
}
