<?php
namespace wcf\data\option\category;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes option categories-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option.category
 * @category 	Community Framework
 */
class OptionCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\option\category\OptionCategoryEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$createPermissions
	 */
	protected $createPermissions = array('admin.system.canEditOption');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$deletePermissions
	 */
	protected $deletePermissions = array('admin.system.canEditOption');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$updatePermissions
	 */
	protected $updatePermissions = array('admin.system.canEditOption');
}
