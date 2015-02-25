<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Template functions are identical to template blocks, but they have no closing tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
interface IFunctionTemplatePlugin {
	/**
	 * Executes this template function.
	 * 
	 * @param	array					$tagArgs
	 * @param	\wcf\system\template\TemplateEngine	$tplObj
	 * @return	string
	 */
	public function execute($tagArgs, TemplateEngine $tplObj);
}
