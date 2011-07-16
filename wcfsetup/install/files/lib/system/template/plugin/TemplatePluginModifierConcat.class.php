<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;
use wcf\system\template\TemplatePluginModifier;

/**
 * The 'concat' modifier returns the string that results from concatenating the arguments.
 * May have two or more arguments.
 * 
 * Usage:
 * {"left"|concat:$right}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierConcat implements TemplatePluginModifier {
	/**
	 * @see wcf\system\template\TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (count($tagArgs) < 2) {
			throw new SystemException("concat modifier needs two or more arguments", 12001);
		}
		
		$result = '';
		foreach ($tagArgs as $arg) {
			$result .= $arg;
		}
	
		return $result;	
	}
}
