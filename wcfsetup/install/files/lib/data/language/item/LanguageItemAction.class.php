<?php
namespace wcf\data\language\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes language item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category 	Community Framework
 */
class LanguageItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\item\LanguageItemEditor';
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.language.canAddLanguage');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.language.canDeleteLanguage');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.language.canEditLanguage');
}
