<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Compiler functions are called during the compilation of a template.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
interface ICompilerTemplatePlugin {
	/**
	 * Executes the start tag of this compiler function.
	 * 
	 * @param	array						$tagArgs
	 * @param	\wcf\system\template\TemplateScriptingCompiler	$compiler
	 * @return	string
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler);
	
	/**
	 * Executes the end tag of this compiler function.
	 * 
	 * @param	\wcf\system\template\TemplateScriptingCompiler	$compiler
	 * @return	string
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler);
}
