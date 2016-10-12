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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class ConcatModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * @inheritDoc
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
