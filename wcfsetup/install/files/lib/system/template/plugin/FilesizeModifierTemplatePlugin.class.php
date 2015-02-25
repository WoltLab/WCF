<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\FileUtil;

/**
 * Template modifier plugin which formats a filesize (given in bytes).
 * 
 * Usage:
 * 	{$string|filesize}
 * 	{123456789|filesize}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class FilesizeModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return FileUtil::formatFilesize($tagArgs[0]);
	}
}
