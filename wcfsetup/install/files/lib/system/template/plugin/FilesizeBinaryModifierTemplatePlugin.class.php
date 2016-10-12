<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\FileUtil;

/**
 * Template modifier plugin which formats a binary filesize (given in bytes).
 * 
 * Usage:
 * 	{$string|filesizeBinary}
 * 	{123456789|filesizeBinary}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class FilesizeBinaryModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		return FileUtil::formatFilesizeBinary($tagArgs[0]);
	}
}
