<?php
namespace wcf\system\template\plugin;
use wcf\data\option\Option;
use wcf\system\template\TemplateEngine;
use wcf\system\template\IModifierTemplatePlugin;

/**
 * The 'arrayfromlist' modifier generates an associative array out of a key-value list.
 * The list has key-value pairs separated by : with each pair on an own line:
 * 
 * Example list:
 * key1:value1
 * key2:value2
 * ...
 * 
 * Usage:
 * {$list|arrayfromlist}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class ArrayfromlistModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see wcf\system\template\IModifierTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// TODO: doesn't work
		// return Option::parseSelectOptions($tagArgs[0]);
		return '';
	}
}
