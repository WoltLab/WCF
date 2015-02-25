<?php
namespace wcf\system\template;

/**
 * Loads and displays template during the setup process.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class SetupTemplateEngine extends TemplateEngine {
	/**
	 * @see	\wcf\system\template\TemplateEngine::loadTemplateGroupCache()
	 */
	protected function loadTemplateGroupCache() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getCompiler()
	 */
	public function getCompiler() {
		if ($this->compilerObj === null) {
			$this->compilerObj = new SetupTemplateCompiler($this);
		}
		
		return $this->compilerObj;
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getSourceFilename()
	 */
	public function getSourceFilename($templateName, $application) {
		return $this->compileDir.'setup/template/'.$templateName.'.tpl';
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getCompiledFilename()
	 */
	public function getCompiledFilename($templateName, $application) {
		return $this->compileDir.'setup/template/compiled/'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * @see	\wcf\system\template\TemplateEngine::getMetaDataFilename()
	 */
	public function getMetaDataFilename($templateName) {
		return $this->compileDir.'setup/template/compiled/'.$this->languageID.'_'.$templateName.'.meta.php';
	}
}
