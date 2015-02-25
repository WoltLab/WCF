<?php
namespace wcf\acp\form;

/**
 * Shows the category edit form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category	Community Framework
 */
class SmileyCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.list';
	
	/**
	 * @see	\wcf\acp\form\AbstractCategoryAddForm::$objectTypeName
	 */
	public $objectTypeName = 'com.woltlab.wcf.bbcode.smiley';
	
	/**
	 * @see	\wcf\acp\form\AbstractCategoryAddForm::$pageTitle
	 */
	public $pageTitle = 'wcf.acp.smiley.category.edit';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_SMILEY');
}
