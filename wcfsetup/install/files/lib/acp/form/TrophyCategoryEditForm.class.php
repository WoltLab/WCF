<?php
namespace wcf\acp\form;

/**
 * Represents the trophy category edit form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class TrophyCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.trophy.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.trophy.category';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TROPHY'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.trophy.canManageTrophy'];
}
