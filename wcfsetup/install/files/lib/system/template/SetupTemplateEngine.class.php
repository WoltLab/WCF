<?php
namespace wcf\system\template;
use wcf\system\exception\SystemException;

/**
 * SetupTemplateEngine loads and displays template in the setup process.
 *
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class SetupTemplateEngine extends TemplateEngine {
	/**
	 * @see wcf\system\template\TemplateEngine::loadTemplateGroupCache()
	 */
	protected function loadTemplateGroupCache() {}
	
	/**
	 * @see wcf\system\template\TemplateEngine::getSourceFilename()
	 */
	public function getSourceFilename($templateName, $packageID) {
		return $this->templatePaths[PACKAGE_ID].'setup/template/'.$templateName.'.tpl';
	}
	
	/**
	 * @see wcf\system\template\TemplateEngine::getCompiledFilename()
	 */
	public function getCompiledFilename($templateName, $packageID) {
		return $this->compileDir.'setup/template/compiled/'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * @see	wcf\system\template\TemplateEngine::getPackageID()
	 */
	public function getPackageID($templateName, $packageID) {
		$path = $this->templatePaths[PACKAGE_ID].'setup/template/'.$templateName.'.tpl';
		if (file_exists($path)) {
			return PACKAGE_ID;
		}
		
		throw new SystemException("Unable to find template '$templateName'", 12005);
	}
	
	/**
	 * @see wcf\system\template\TemplateEngine::getCompiler()
	 */
	protected function getCompiler() {
		return new TemplateCompiler($this);
	}
	
	/**
	 * Template Listener are not available during setup.
	 */
	protected function loadTemplateListeners() {}
}
