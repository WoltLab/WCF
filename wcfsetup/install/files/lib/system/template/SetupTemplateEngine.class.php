<?php
namespace wcf\system\template;
use wcf\system\exception\SystemException;

/**
 * Loads and displays template during the setup process.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
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
	public function getSourceFilename($templateName, $application) {
		$sourceFilename = $this->templatePaths[$application].'setup/template/'.$templateName.'.tpl';
		if (!empty($sourceFilename)) {
			return $sourceFilename;
		}
		
		throw new SystemException("Unable to find template '".$templateName."'");
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
	 * @see	wcf\system\template\TemplateEngine::loadTemplateListeners()
	 */
	protected function loadTemplateListeners() {
		// template isteners are not available during setup
	}
}
