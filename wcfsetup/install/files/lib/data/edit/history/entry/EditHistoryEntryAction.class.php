<?php
namespace wcf\data\edit\history\entry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;

/**
 * Executes edit history entry-related actions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.edit.history.entry
 * @category	Community Framework
 */
class EditHistoryEntryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\edit\history\entry\EditHistoryEntryEditor';
	
	/**
	 * Checks permissions to revert.
	 */
	public function validateRevert() {
		if (!MODULE_EDIT_HISTORY) {
			throw new IllegalLinkException();
		}
		
		$historyEntry = $this->getSingleObject();
		
		$objectType = ObjectTypeCache::getInstance()->getObjectType($historyEntry->objectTypeID);
		$processor = $objectType->getProcessor();
		$object = $this->getSingleObject()->getObject();
		$processor->checkPermissions($object);
	}
	
	/**
	 * Reverts the objects back to this history entry.
	 */
	public function revert() {
		$this->getSingleObject()->getObject()->revertVersion($this->getSingleObject()->getDecoratedObject());
	}
}
