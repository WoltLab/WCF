<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Template prefilter plugin which compiles static language variables for the assignment in javascript code.
 *
 * Usage:
 * 	{jslang}wcf.foo.bar{/jslang}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since       5.3
 */
class JslangPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		$sourceContent = preg_replace_callback("~{$ldq}jslang{$rdq}([\w\.]+){$ldq}/jslang{$rdq}~", function ($match) {
			$value = WCF::getLanguage()->get($match[1]);
			if (strpos($value, '{') !== false) {
				$variableName = '__jslang_capture_' . substr(StringUtil::getRandomID(), 0, 8);
				$value = "{capture assign='" . $variableName . "'}" . $value . "{/capture}{@$" . $variableName . "|encodeJS}";
			}
			else {
				$value = StringUtil::encodeJS($value);
			}
			
			return $value;
		}, $sourceContent);
		
		return $sourceContent;
	}
}
