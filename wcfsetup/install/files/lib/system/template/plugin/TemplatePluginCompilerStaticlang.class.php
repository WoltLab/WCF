<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplatePluginCompiler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'staticlang' compiler function gets the source of a language variables.
 * 
 * Usage:
 * {staticlang}$blah{/staticlang}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerStaticlang implements TemplatePluginCompiler {
	/**
	 * @see TemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('staticlang');
		
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see TemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('staticlang');
		$hash = StringUtil::getRandomID();
		return "<?php \$_lang".$hash." = ob_get_contents(); ob_end_clean(); echo WCF::getLanguage()->get(\$_lang".$hash."); ?>";
	}
}
