<?php
namespace wcf\system\template;

/**
 * ACPTemplate loads and displays template in the admin control panel of the wcf.
 * ACPTemplate does not support template groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class ACPTemplateEngine extends TemplateEngine {
	/**
	 * @see	wcf\system\template\TemplateEngine::$environment
	 */
	protected $environment = 'admin';
	
	/**
	 * @see	wcf\system\template\TemplateEngine::__construct()
	 */
	protected function init() {
		parent::init();
		
		$this->templatePaths = array(1 => WCF_DIR.'acp/templates/');
		$this->compileDir = WCF_DIR.'acp/templates/compiled/';
		
		if (!defined('NO_IMPORTS')) {
			$this->loadTemplateListeners();
		}
	}
	
	/**
	 * Deletes all compiled acp templates.
	 * 
	 * @param 	string		$compileDir
	 */
	public static function deleteCompiledACPTemplates($compileDir = '') {
		if (empty($compileDir)) $compileDir = WCF_DIR.'acp/templates/compiled/';
		
		self::deleteCompiledTemplates($compileDir);
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::setTemplateGroupID()
	 */
	public final function setTemplateGroupID($templateGroupID) {
		// template groups are not supported by the acp template engine
		$this->templateGroupID = 0;
	}
}
