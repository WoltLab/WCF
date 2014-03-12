<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Modifiers are functions that are applied to a variable in the template before
 * it is displayed or used in some other context.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category	Community Framework
 */
interface IModifierTemplatePlugin {
	/**
	 * Executes this modifier.
	 * 
	 * @param	array					$tagArgs
	 * @param	\wcf\system\template\TemplateEngine	$tplObj
	 * @return	string
	 */
	public function execute($tagArgs, TemplateEngine $tplObj);
}
