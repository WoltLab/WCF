<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template modifier plugin which wordwraps a string.
 * 
 * Usage:
 * 	{$foo|wordwrap:50:' '}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class WordwrapModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// default values
		$width = 50;
		$break = ' ';
		
		// get values
		$string = $tagArgs[0];
		if (isset($tagArgs[1])) $width = intval($tagArgs[1]);
		if (isset($tagArgs[2])) $break = $tagArgs[2];
		
		return StringUtil::wordwrap($string, $width, $break);
	}
}
