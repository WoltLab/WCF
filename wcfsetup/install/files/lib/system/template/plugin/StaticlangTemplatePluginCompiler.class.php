<?php
namespace wcf\system\template\plugin;
use wcf\system\template\ICompilerTemplatePlugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'staticlang' compiler function gets the source of a language variables.
 * 
 * Usage:
 * {staticlang}$blah{/staticlang}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class StaticlangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('staticlang');
		
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('staticlang');
		$hash = StringUtil::getRandomID();
		return "<?php \$_lang".$hash." = ob_get_contents(); ob_end_clean(); echo \wcf\system\WCF::getLanguage()->get(\$_lang".$hash."); ?>";
	}
}
