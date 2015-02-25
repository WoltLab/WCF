<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template compiler plugin which appends a value to a template variable.
 * 
 * Usage:
 * 	{append var=name value="foo"}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class AppendCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see	\wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['var'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'var' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		if (!isset($tagArgs['value'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'value' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
				
		return "<?php \$this->append(".$tagArgs['var'].", ".$tagArgs['value']."); ?>";
	}
	
	/**
	 * @see	\wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/append}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
	}
}
