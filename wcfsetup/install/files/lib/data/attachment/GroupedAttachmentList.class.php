<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a grouped list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.attachment
 * @category	Community Framework
 */
class GroupedAttachmentList extends AttachmentList {
	/**
	 * grouped objects
	 * @var	array
	 */
	public $groupedObjects = array();
	
	/**
	 * object type
	 * @var	\wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 0;
	
	/**
	 * wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'attachment.showOrder';
	
	/**
	 * Creates a new GroupedAttachmentList object.
	 * 
	 * @param	string		$objectType
	 */
	public function __construct($objectType) {
		parent::__construct();
		
		$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $objectType);
		$this->getConditionBuilder()->add('attachment.objectTypeID = ?', array($this->objectType->objectTypeID));
		
		$this->getConditionBuilder()->add('(SELECT embeddedObjectID FROM wcf'.WCF_N.'_message_embedded_object WHERE messageObjectTypeID = ? AND messageID = attachment.objectID AND embeddedObjectTypeID = ? AND embeddedObjectID = attachment.attachmentID) IS NULL', array(
			ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $objectType),
			ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', 'com.woltlab.wcf.attachment')
		));
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		// group by object id
		foreach ($this->objects as $attachmentID => $attachment) {
			if (!isset($this->groupedObjects[$attachment->objectID])) {
				$this->groupedObjects[$attachment->objectID] = array();
			}
			
			$this->groupedObjects[$attachment->objectID][$attachmentID] = $attachment;
		}
	}
	
	/**
	 * Sets the permissions for attachment access.
	 * 
	 * @param	array<boolean>		$permissions
	 */
	public function setPermissions(array $permissions) {
		foreach ($this->objects as $attachment) {
			$attachment->setPermissions($permissions);
		}
	}
	
	/**
	 * Returns the objects of the list.
	 * 
	 * @return	array<\wcf\data\DatabaseObject>
	 */
	public function getGroupedObjects($objectID) {
		if (isset($this->groupedObjects[$objectID])) {
			return $this->groupedObjects[$objectID];
		}
		
		return array();
	}
}
