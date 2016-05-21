<?php
namespace wcf\data\user\option\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user option category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category	Community Framework
 */
class UserOptionCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = UserOptionCategoryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
