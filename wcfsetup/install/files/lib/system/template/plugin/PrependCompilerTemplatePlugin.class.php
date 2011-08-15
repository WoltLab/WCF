<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\ICompilerTemplatePlugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'prepend' compiler function calls the prepend function on the template object.
 * 
 * Usage:
 * {prepend var=name value="foo"}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class PrependCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['var'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'var' argument in prepend tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		if (!isset($tagArgs['value'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'value' argument in prepend tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
				
		return "<?php \$this->prepend(".$tagArgs['var'].", ".$tagArgs['value']."); ?>";
	}
	
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/prepend}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
	}
}
