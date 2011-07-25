<?php
namespace wcf\system\template;

/**
 * Prefilters are used to process the source of the template immediately before compilation.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface ITemplatePluginPrefilter {
	/**
	 * Executes this prefilter.
	 * 
	 * @param	string				$templateName
	 * @param	string				$sourceContent	
	 * @param	TemplateScriptingCompiler 	$compiler	
	 * @return 	string				$sourceContent
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler);
}
