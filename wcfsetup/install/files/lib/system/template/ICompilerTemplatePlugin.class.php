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
interface ICompilerTemplatePlugin {
	/**
	 * Executes the start tag of this compiler function.
	 * 
	 * @param	array		$tagArgs		
	 * @param	wcf\system\template\TemplateScriptingCompiler	$compiler
	 * @return	string		php code	
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler);
	
	/**
	 * Executes the end tag of this compiler function.
	 * 
	 * @param	wcf\system\template\TemplateScriptingCompiler	$compiler
	 * @return	string		php code	
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler);
}
