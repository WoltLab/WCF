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
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class LangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('lang');
		
		$tagArgs = $compiler::makeArgString($tagArgs);
		return "<?php
			\$this->tagStack[] = ['lang', array_merge(\$this->v, [$tagArgs])];
			ob_start();
			?>";
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('lang');
		return "<?php
			echo wcf\system\WCF::getLanguage()->tplGet(ob_get_clean(), \$this->tagStack[count(\$this->tagStack) - 1][1]);
			array_pop(\$this->tagStack); ?>";
	}
}
