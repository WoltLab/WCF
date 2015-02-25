<?php
namespace wcf\system\attachment;
use wcf\data\attachment\AttachmentAction;
use wcf\data\attachment\AttachmentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Handles uploaded attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.attachment
 * @category	Community Framework
 */
class AttachmentHandler implements \Countable {
	/**
	 * object type
	 * @var	\wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * object type
	 * @var	\wcf\system\attachment\IAttachmentObjectType
	 */
	protected $processor = null;
	
	/**
	 * object id
	 * @var	integer
	 */
	protected $objectID = 0;
	
	/**
	 * parent object id
	 * @var	integer
	 */
	protected $parentObjectID = 0;
	
	/**
	 * temp hash
	 * @var	string
	 */
	protected $tmpHash = '';
	
	/**
	 * list of attachments
	 * @var	\wcf\data\attachment\AttachmentList
	 */
	protected $attachmentList = null;
	
	/**
	 * Creates a new AttachmentHandler object.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$tmpHash
	 */
	public function __construct($objectType, $objectID, $tmpHash = '', $parentObjectID = 0) {
		if (!$objectID && !$tmpHash) {
			throw new SystemException('objectID and tmpHash cannot be empty at the same time');
		}
		
		$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $objectType);
		$this->processor = $this->objectType->getProcessor();
		$this->objectID = $objectID;
		$this->parentObjectID = $parentObjectID;
		$this->tmpHash = $tmpHash;
	}
	
	/**
	 * Returns a list of attachments.
	 * 
	 * @return	\wcf\data\attachment\AttachmentList
	 */
	public function getAttachmentList() {
		if ($this->attachmentList === null) {
			$this->attachmentList = new AttachmentList();
			$this->attachmentList->sqlOrderBy = 'attachment.showOrder';
			$this->attachmentList->getConditionBuilder()->add('objectTypeID = ?', array($this->objectType->objectTypeID));
			if ($this->objectID) {
				$this->attachmentList->getConditionBuilder()->add('objectID = ?', array($this->objectID));
			}
			else {
				$this->attachmentList->getConditionBuilder()->add('tmpHash = ?', array($this->tmpHash));
			}
			$this->attachmentList->readObjects();
		}
		
		return $this->attachmentList;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->getAttachmentList());
	}
	
	/**
	 * Sets the object id of temporary saved attachments.
	 * 
	 * @param	integer		$objectID
	 */
	public function updateObjectID($objectID) {
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	objectID = ?,
				tmpHash = ''
			WHERE	objectTypeID = ?
				AND tmpHash = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID, $this->objectType->objectTypeID, $this->tmpHash));
	}
	
	/**
	 * Transfers attachments to a different object id of the same type (e.g. merging content)
	 * 
	 * @param	string		$objectType
	 * @param	integer		$newObjectID
	 * @param	array<integer>	$oldObjectIDs
	 */
	public static function transferAttachments($objectType, $newObjectID, array $oldObjectIDs) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array(ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $objectType)->objectTypeID));
		$conditions->add("objectID IN (?)", array($oldObjectIDs));
		$parameters = $conditions->getParameters();
		array_unshift($parameters, $newObjectID);
		
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	objectID = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($parameters);
	}
	
	/**
	 * Removes all attachments for given object ids by type.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public static function removeAttachments($objectType, array $objectIDs) {
		$attachmentList = new AttachmentList();
		$attachmentList->getConditionBuilder()->add("objectTypeID = ?", array(ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', $objectType)->objectTypeID));
		$attachmentList->getConditionBuilder()->add("objectID IN (?)", array($objectIDs));
		$attachmentList->readObjects();
		
		if (count($attachmentList)) {
			$attachmentAction = new AttachmentAction($attachmentList->getObjects(), 'delete');
			$attachmentAction->executeAction();
		}
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getMaxSize()
	 */
	public function getMaxSize() {
		return $this->processor->getMaxSize();
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getAllowedExtensions()
	 */
	public function getAllowedExtensions() {
		return $this->processor->getAllowedExtensions();
	}
	
	/**
	 * Returns a formatted list of the allowed file extensions.
	 * 
	 * @return	array<string>
	 */
	public function getFormattedAllowedExtensions() {
		$extensions = $this->getAllowedExtensions();
		
		// sort
		sort($extensions);
		
		// check wildcards
		for ($i = 0, $j = count($extensions); $i < $j; $i++) {
			if (strpos($extensions[$i], '*') !== false) {
				for ($k = $j - 1; $k > $i; $k--) {
					if (preg_match('/^'.str_replace('\*', '.*', preg_quote($extensions[$i], '/')).'$/i', $extensions[$k])) {
						array_splice($extensions, $k, 1);
						$j--;
					}
				}
			}
		}
		
		return $extensions;
	}
	
	/**
	 * @see	\wcf\system\attachment\IAttachmentObjectType::getMaxCount()
	 */
	public function getMaxCount() {
		return $this->processor->getMaxCount();
	}
	
	/**
	 * Returns true if the active user has the permission to upload attachments.
	 * 
	 * @return	boolean
	 */
	public function canUpload() {
		return $this->processor->canUpload($this->objectID, $this->parentObjectID);
	}
	
	/**
	 * Returns the object type processor.
	 * 
	 * @return	\wcf\system\attachment\IAttachmentObjectType
	 */
	public function getProcessor() {
		return $this->processor;
	}
}
