<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Template functions are identical to template blocks, but they have no closing tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
interface IFunctionTemplatePlugin {
	/**
	 * Executes this template function.
	 * 
	 * @param	array			$tagArgs
	 * @param	TemplateEngine		$tplObj
	 * @return	string
	 */
	public function execute($tagArgs, TemplateEngine $tplObj);
}
