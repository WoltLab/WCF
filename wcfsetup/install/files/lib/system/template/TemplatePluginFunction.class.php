<?php
namespace wcf\system\template;

/**
 * Template functions are identical to template blocks, but they have no closing tag.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface TemplatePluginFunction {
	/**
	 * Executes this template function.
	 * 
	 * @param	array			$tagArgs
	 * @param	TemplateEngine		$tplObj
	 * @return	string				output
	 */
	public function execute($tagArgs, TemplateEngine $tplObj);
}
?>