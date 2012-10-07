<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * The 'icon' compiler function compiles dynamic icon paths.
 *
 * Usage:
 * {icon}{$foo}{/icon}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class IconCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('icon');
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('icon');
		return "<?php echo wcf\system\style\StyleHandler::getInstance()->getStyle()->getIconPath(ob_get_clean()); ?>";
	}
}
