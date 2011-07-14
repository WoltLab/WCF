<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\template\TemplatePluginModifier;
use wcf\util\FileUtil;

/**
 * The 'filesize' modifier formats a filesize (given in bytes).
 * 
 * Usage:
 * {$string|filesize}
 * {123456789|filesize}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierFilesize implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return FileUtil::formatFilesize($tagArgs[0]);
	}
}
?>