<?php
namespace wcf\system\template;

/**
 * Block functions encloses a template block and operate on the contents of this block.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface ITemplatePluginBlock {
	/**
	 * Executes this template block.
	 * 
	 * @param	array			$tagArgs
	 * @param	string			$blockContent
	 * @param	TemplateEngine 		$tplObj
	 * @return	string			output
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj);
	
	/**
	 * Initialises this template block.
	 * 
	 * @param	array			$tagArgs
	 * @param	TemplateEngine		$tplObj
	 */
	public function init($tagArgs, TemplateEngine $tplObj);
	
	/**
	 * This function is called before every execution of this block function.
	 * 
	 * @param	TemplateEngine		$tplObj
	 * @return	boolean
	 */
	public function next(TemplateEngine $tplObj);
}
