<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplatePluginCompiler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'icon' compiler function compiles dynamic icon paths.
 *
 * Usage:
 * {icon}{$foo}{/icon}
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerIcon implements TemplatePluginCompiler {
	/**
	 * @see TemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('icon');
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see TemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('icon');
		$hash = StringUtil::getRandomID();
		return "<?php \$_icon".$hash." = ob_get_contents(); ob_end_clean(); echo wcf\system\style\StyleHandler::getInstance()->getStyle()->getIconPath(\$_icon".$hash."); ?>";
	}
}
?>
