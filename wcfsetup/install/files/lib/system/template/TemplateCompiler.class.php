<?php
namespace wcf\system\template;
use wcf\system\io\File;

/**
 * Compiles template source into valid PHP code.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
class TemplateCompiler extends TemplateScriptingCompiler {
	/**
	 * Compiles the source of a template.
	 * 
	 * @param	string		$templateName
	 * @param	string		$sourceContent
	 * @param	string		$compiledFilename
	 * @param	array		$metaData
	 */
	public function compile($templateName, $sourceContent, $compiledFilename, $metaData) {
		// build fileheader for template
		$compiledHeader = "<?php\n/**\n * WoltLab Community Framework\n * Template: ".$templateName."\n * Compiled at: ".gmdate('r')."\n * \n * DO NOT EDIT THIS FILE\n */\n\$this->v['tpl']['template'] = '".addcslashes($templateName, "'\\")."';\n?>\n";
		
		// include plug-ins
		$compiledContent = $this->compileString($templateName, $sourceContent, $metaData);
		
		// write compiled template to file
		$file = new File($compiledFilename);
		$file->write($compiledHeader.$compiledContent['template']);
		$file->close();
		
		// write meta data to file
		$this->saveMetaData($templateName, $metaData['filename'], $compiledContent['meta']);
	}
	
	/**
	 * Saves meta data for given template.
	 * 
	 * @param	string		$templateName
	 * @param	string		$filename
	 * @param	string		$content
	 */
	public function saveMetaData($templateName, $filename, $content) {
		$file = new File($filename);
		$file->write("<?php exit; /* meta data for template: ".$templateName." (generated at ".gmdate('r').") DO NOT EDIT THIS FILE */ ?>\n");
		$file->write(serialize($content));
		$file->close();
	}
	
	/**
	 * Returns the name of the current template.
	 * 
	 * @return	string
	 */
	public function getCurrentTemplate() {
		return $this->getCurrentIdentifier();
	}
}
