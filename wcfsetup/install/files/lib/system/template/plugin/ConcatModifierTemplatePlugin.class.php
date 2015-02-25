<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * Template modifier plugin which returns the string that results from concatenating
 * the arguments. May have two or more arguments.
 * 
 * Usage:
 * 	{"left"|concat:$right}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class ConcatModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @see	\wcf\system\template\ITemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (count($tagArgs) < 2) {
			throw new SystemException("concat modifier needs two or more arguments");
		}
		
		$result = '';
		foreach ($tagArgs as $arg) {
			$result .= $arg;
		}
		
		return $result;
	}
}
