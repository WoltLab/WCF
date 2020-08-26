<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template compiler plugin which compiles dynamic language variables for the assignment in javascript code.
 *
 * Usage:
 * 	{jslang}$blah{/jslang}
 * 	{jslang var=$x}foo{/jslang}
 *
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since       5.3
 */
class JslangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('jslang');
		
		$tagArgs = $compiler::makeArgString($tagArgs);
		return "<?php
			\$this->tagStack[] = ['jslang', array_merge(\$this->v, [$tagArgs])];
			ob_start();
			?>";
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('jslang');
		return "<?php
			echo wcf\util\StringUtil::encodeJS(wcf\system\WCF::getLanguage()->tplGet(ob_get_clean(), \$this->tagStack[count(\$this->tagStack) - 1][1]));
			array_pop(\$this->tagStack); ?>";
	}
}
