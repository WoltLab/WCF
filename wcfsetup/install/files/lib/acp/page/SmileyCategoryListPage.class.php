<?php
namespace wcf\acp\page;

/**
 * Shows the smiley category list.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category	Community Framework
 */
class SmileyCategoryListPage extends AbstractCategoryListPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.list';
	
	/**
	 * @see	\wcf\acp\page\AbstractCategoryListPage::$activeMenuItem
	 */
	public $objectTypeName = 'com.woltlab.wcf.bbcode.smiley';
	
	/**
	 * @see	\wcf\acp\page\AbstractCategoryListPage::$pageTitle
	 */
	public $pageTitle = 'wcf.acp.smiley.category.list';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_SMILEY');
}
