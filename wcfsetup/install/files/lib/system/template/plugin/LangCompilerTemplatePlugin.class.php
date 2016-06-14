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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class LangCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('lang');
		
		$tagArgs = $compiler->makeArgString($tagArgs);
		return "<?php
			\$this->tagStack[] = array('lang', array($tagArgs));
			ob_start();
			?>";
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('lang');
		return "<?php
			\$__langCompilerTemplatePluginOutput = (
				!empty(\$this->tagStack[count(\$this->tagStack) - 1][1]['__literal'])
				?
				wcf\system\WCF::getLanguage()->get(
					ob_get_clean(),
					\$this->tagStack[count(\$this->tagStack) - 1][1],
					(
						isset(\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional'])
						?
						\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional']
						:
						false
					)
				)
				:
				wcf\system\WCF::getLanguage()->getDynamicVariable(
					ob_get_clean(),
					\$this->tagStack[count(\$this->tagStack) - 1][1],
					(
						isset(\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional'])
						?
						\$this->tagStack[count(\$this->tagStack) - 1][1]['__optional']
						:
						false
					)
				)
			);
			
			if (!empty(\$this->tagStack[count(\$this->tagStack) - 1][1]['__encode'])) \$__langCompilerTemplatePluginOutput = wcf\util\StringUtil::encodeHTML(\$__langCompilerTemplatePluginOutput);
			echo \$__langCompilerTemplatePluginOutput;
			
			array_pop(\$this->tagStack); ?>";
	}
}
