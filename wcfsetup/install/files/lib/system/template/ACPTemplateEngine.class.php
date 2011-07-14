<?php
namespace wcf\system\template;

/**
 * ACPTemplate loads and displays template in the admin control panel of the wcf.
 * ACPTemplate does not support template groups.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class ACPTemplateEngine extends TemplateEngine {
	/**
	 * @see	TemplateEngine::$environment
	 */
	protected $environment = 'admin';
	
	/**
	 * @see	TemplateEngine::__construct()
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
	 * Template groups are not supported by acp template engine.
	 * 
	 * @see	TemplateEngine::setTemplateGroupID()
	 */
	public final function setTemplateGroupID($templateGroupID) {
		$this->templateGroupID = 0;
	}
}
?>
