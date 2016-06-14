<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template compiler plugin which assigns a certain value to a template variable.
 * 
 * Usage:
 * 	{assign var=name value="foo"}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class AssignCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['var'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'var' argument in assign tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		if (!isset($tagArgs['value'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'value' argument in assign tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
				
		return "<?php \$this->assign(".$tagArgs['var'].", ".$tagArgs['value']."); ?>";
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/assign}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
	}
}
