<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Prefilters are used to process the source of the template immediately before compilation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
interface IPrefilterTemplatePlugin {
	/**
	 * Executes this prefilter.
	 * 
	 * @param	string						$templateName
	 * @param	string						$sourceContent
	 * @param	\wcf\system\template\TemplateScriptingCompiler	$compiler
	 * @return	string
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler);
}
