<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template compiler plugin which gets the source of a language variables.
 * 
 * Usage:
 * 	{staticlang}$blah{/staticlang}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
// TODO: Is the classname or the filename correct?
class StaticlangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see	wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('staticlang');
		
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see	wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('staticlang');
		return "<?php echo \wcf\system\WCF::getLanguage()->get(ob_get_clean()); ?>";
	}
}
