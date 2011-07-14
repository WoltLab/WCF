<?php
namespace wcf\system\template;

/**
 * Compiler functions are called during the compilation of a template.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface TemplatePluginCompiler {
	/**
	 * Executes the start tag of this compiler function.
	 * 
	 * @param	array				$tagArgs		
	 * @param	TemplateScriptingCompiler	$compiler
	 * @return	string						php code	
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler);
	
	/**
	 * Executes the end tag of this compiler function.
	 * 
	 * @param	TemplateScriptingCompiler	$compiler	
	 * @return	string						php code	
	 */
	public function executeEnd(TemplateScriptingCompiler $tplObj);
}
?>