<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Block functions encloses a template block and operate on the contents of this block.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
interface IBlockTemplatePlugin {
	/**
	 * Executes this template block.
	 * 
	 * @param	array					$tagArgs
	 * @param	string					$blockContent
	 * @param	\wcf\system\template\TemplateEngine	$tplObj
	 * @return	string
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj);
	
	/**
	 * Initialises this template block.
	 * 
	 * @param	array					$tagArgs
	 * @param	\wcf\system\template\TemplateEngine	$tplObj
	 */
	public function init($tagArgs, TemplateEngine $tplObj);
	
	/**
	 * This function is called before every execution of this block function.
	 * 
	 * @param	\wcf\system\template\TemplateEngine	$tplObj
	 * @return	boolean
	 */
	public function next(TemplateEngine $tplObj);
}
