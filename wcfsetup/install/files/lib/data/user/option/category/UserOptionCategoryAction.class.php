<?php
namespace wcf\data\user\option\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user option category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Option\Category
 * 
 * @method	UserOptionCategory		create()
 * @method	UserOptionCategoryEditor[]	getObjects()
 * @method	UserOptionCategoryEditor	getSingleObject()
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
