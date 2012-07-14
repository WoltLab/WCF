<?php
namespace wcf\data\language\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes language category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.category
 * @category 	Community Framework
 */
class LanguageCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\language\category\LanguageCategoryEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.language.canAddLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.language.canDeleteLanguage');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.language.canEditLanguage');
}
