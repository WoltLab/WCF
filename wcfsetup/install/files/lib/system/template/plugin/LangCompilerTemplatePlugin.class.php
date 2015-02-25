<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template compiler plugin which compiles dynamic language variables.
 * 
 * Usage:
 * 	{lang}$blah{/lang}
 * 	{lang var=$x}foo{/lang}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class LangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see	\wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('lang');
		
		$tagArgs = $compiler->makeArgString($tagArgs);
		return "<?php \$this->tagStack[] = array('lang', array($tagArgs)); ob_start(); ?>";
	}
	
	/**
	 * @see	\wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('lang');
		return "<?php echo (!empty(\$this->tagStack[count(\$this->tagStack) - 1][1]['__literal']) ? wcf\system\WCF::getLanguage()->get(ob_get_clean(), \$this->tagStack[count(\$this->tagStack) - 1][1], (isset(\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional']) ? \$this->tagStack[count(\$this->tagStack) - 1][1]['__optional'] : false)) : wcf\system\WCF::getLanguage()->getDynamicVariable(ob_get_clean(), \$this->tagStack[count(\$this->tagStack) - 1][1], (isset(\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional']) ? \$this->tagStack[count(\$this->tagStack) - 1][1]['__optional'] : false))); array_pop(\$this->tagStack); ?>";
	}
}
