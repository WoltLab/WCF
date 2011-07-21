<?php
namespace wcf\system\template\plugin;
use wcf\system\template\ITemplatePluginCompiler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\exception\SystemException;

/**
 * The 'append' compiler function calls the append function on the template object.
 * 
 * Usage:
 * {append var=name value="foo"}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerAppend implements ITemplatePluginCompiler {
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['var'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'var' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12001);
		}
		if (!isset($tagArgs['value'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'value' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12001);
		}
				
		return "<?php \$this->append(".$tagArgs['var'].", ".$tagArgs['value']."); ?>";
	}
	
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/append}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12003);
	}
}
