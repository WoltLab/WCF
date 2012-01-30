<?php
namespace wcf\system\template\plugin;
use wcf\system\WCF;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * The 'lang' prefilter compiles static language variables.
 * Dynamic language variables will catched by the 'lang' compiler function.
 * It is recommended to use static language variables.
 * 
 * Usage:
 * {lang}foo{/lang}
 * {lang}lang.foo.bar{/lang}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class LangPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @see wcf\system\template\IPrefilterTemplatePlugin::execute()
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		$sourceContent = preg_replace_callback("~{$ldq}lang{$rdq}([\w\.]+){$ldq}/lang{$rdq}~", function ($match) {
			return WCF::getLanguage()->get($match[1]);
		}, $sourceContent);

		return $sourceContent;
	}
}
