<?php
namespace wcf\system\template;
use wcf\system\exception\SystemException;

/**
 * Loads and displays template during the setup process.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class SetupTemplateEngine extends TemplateEngine {
	/**
	 * @see	wcf\system\template\TemplateEngine::loadTemplateGroupCache()
	 */
	protected function loadTemplateGroupCache() {
		// does nothing
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::getSourceFilename()
	 */
	public function getSourceFilename($templateName, $packageID) {
		return $this->templatePaths[PACKAGE_ID].'setup/template/'.$templateName.'.tpl';
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::getCompiledFilename()
	 */
	public function getCompiledFilename($templateName) {
		return $this->compileDir.'setup/template/compiled/'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::getMetaDataFilename()
	 */
	public function getMetaDataFilename($templateName) {
		return $this->compileDir.'setup/template/compiled/'.$this->languageID.'_'.$templateName.'.meta.php';
	}

	/**
	 * @see	wcf\system\template\TemplateEngine::getPackageID()
	 */
	public function getPackageID($templateName, $application = 'wcf') {
		$path = $this->templatePaths[PACKAGE_ID].'setup/template/'.$templateName.'.tpl';
		if (file_exists($path)) {
			return PACKAGE_ID;
		}
		
		throw new SystemException("Unable to find template '$templateName'");
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::loadTemplateListeners()
	 */
	protected function loadTemplateListeners() {
		// template isteners are not available during setup
	}
}
