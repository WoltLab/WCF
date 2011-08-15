<?php
namespace wcf\system\template\plugin;
use wcf\system\template\IPrefilterTemplatePlugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * The 'icon' prefilter compiles static icon paths.
 * 
 * Usage:
 * {icon}iconS.png{/icon}
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class IconPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @see wcf\system\template\ITemplatePluginPrefilter::execute()
	 */
	public function execute($sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		$sourceContent = preg_replace("~{$ldq}icon{$rdq}([\w\.]+){$ldq}/icon{$rdq}~", '{literal}<?php echo wcf\system\style\StyleHandler::getInstance()->getStyle()->getIconPath(\'$1\'); ?>{/literal}', $sourceContent);

		return $sourceContent;
	}
}
