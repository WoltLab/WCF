<?php
namespace wcf\system\template;
use wcf\system\application\ApplicationHandler;

/**
 * Loads and displays template in the ACP.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template
 */
class ACPTemplateEngine extends TemplateEngine {
	/**
	 * @inheritDoc
	 */
	protected $environment = 'admin';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		parent::init();
		
		$this->templatePaths = ['wcf' => WCF_DIR.'acp/templates/'];
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
	 * @inheritDoc
	 */
	public function getCompiledFilename($templateName, $application) {
		$abbreviation = 'wcf';
		if (PACKAGE_ID) {
			$abbreviation = ApplicationHandler::getInstance()->getActiveApplication()->getAbbreviation();
		}
		
		return $this->compileDir.$this->templateGroupID.'_'.$abbreviation.'_'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * @inheritDoc
	 */
	public final function setTemplateGroupID($templateGroupID) {
		// template groups are not supported by the acp template engine
		$this->templateGroupID = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateListenerCode($templateName, $eventName) {
		// skip template listeners within WCFSetup
		if (!PACKAGE_ID) {
			return '';
		}
		
		return parent::getTemplateListenerCode($templateName, $eventName);
	}
}
