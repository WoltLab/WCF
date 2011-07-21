<?php
namespace wcf\system\template\plugin;
use wcf\system\template\ITemplatePluginCompiler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'lang' compiler function compiles dynamic language variables.
 * 
 * Usage:
 * {lang}$blah{/lang}
 * {lang var=$x}foo{/lang}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerLang implements ITemplatePluginCompiler {
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('lang');
		
		$newTagArgs = array();
		foreach ($tagArgs as $key => $arg) {
			$newTagArgs[$key] = 'wcf\util\StringUtil::encodeHTML('.$arg.')';
		}
		
		$tagArgs = $compiler->makeArgString($newTagArgs);
		return "<?php \$this->tagStack[] = array('lang', array($tagArgs)); ob_start(); ?>";
	}
	
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('lang');
		$hash = StringUtil::getRandomID();
		return "<?php \$_lang".$hash." = ob_get_contents(); ob_end_clean(); echo wcf\system\WCF::getLanguage()->getDynamicVariable(\$_lang".$hash.", \$this->tagStack[count(\$this->tagStack) - 1][1]); array_pop(\$this->tagStack); ?>";
	}
}
