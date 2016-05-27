<?php
namespace wcf\data\user\group\option\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user group option category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 * 
 * @method	UserGroupOptionCategory			create()
 * @method	UserGroupOptionCategoryEditor[]		getObjects()
 * @method	UserGroupOptionCategoryEditor		getSingleObject()
 */
class UserGroupOptionCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = UserGroupOptionCategoryEditor::class;
}
