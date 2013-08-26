<?php
namespace wcf\system\tagging;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Manages the tagging of objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.tagging
 * @category	Community Framework
 */
class TagEngine extends SingletonFactory {
	/**
	 * Adds tags to a tagged object.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	array		$tags
	 * @param	integer		$languageID
	 * @param	boolean		$replace
	 */
	public function addObjectTags($objectType, $objectID, array $tags, $languageID, $replace = true) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		$tags = array_unique($tags);
		
		// remove tags prior to apply the new ones (prevents duplicate entries)
		if ($replace) {
			$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
				WHERE		objectTypeID = ?
						AND objectID = ?
						AND languageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				$objectID,
				$languageID
			));
		}
		
		// get tag ids
		$tagIDs = array();
		foreach ($tags as $tag) {
			if (empty($tag)) continue;
			
			// enforce max length
			if (mb_strlen($tag) > TAGGING_MAX_TAG_LENGTH) {
				$tag = mb_substr($tag, 0, TAGGING_MAX_TAG_LENGTH);
			}
			
			// find existing tag
			$tagObj = Tag::getTag($tag, $languageID);
			if ($tagObj === null) {
				// create new tag
				$tagAction = new TagAction(array(), 'create', array('data' => array(
					'name' => $tag,
					'languageID' => $languageID
				)));
				
				$tagAction->executeAction();
				$returnValues = $tagAction->getReturnValues();
				$tagObj = $returnValues['returnValues'];
			}
			
			if ($tagObj->synonymFor !== null) $tagIDs[$tagObj->synonymFor] = $tagObj->synonymFor;
			else $tagIDs[$tagObj->tagID] = $tagObj->tagID;
		}
		
		// save tags
		$sql = "INSERT INTO	wcf".WCF_N."_tag_to_object
					(objectID, tagID, objectTypeID, languageID)
			VALUES 		(?, ?, ?, ?)";
		WCF::getDB()->beginTransaction();
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($tagIDs as $tagID) {
			$statement->execute(array($objectID, $tagID, $objectTypeID, $languageID));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Deletes all tags assigned to given tagged object.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	integer		$languageID
	 */
	public function deleteObjectTags($objectType, $objectID, $languageID = null) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
			WHERE		objectTypeID = ?
					AND objectID = ?
					".($languageID !== null ? "AND languageID = ?" : "");
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = array(
			$objectTypeID,
			$objectID
		);
		if ($languageID !== null) $parameters[] = $languageID;
		$statement->execute($parameters);
	}
	
	/**
	 * Deletes all tags assigned to given tagged objects.
	 * 
	 * @param	string			$objectType
	 * @param	array<integer>		$objectIDs
	 */
	public function deleteObjects($objectType, array $objectIDs) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$conditionsBuilder = new PreparedStatementConditionBuilder();
		$conditionsBuilder->add('objectTypeID = ?', array($objectTypeID));
		$conditionsBuilder->add('objectID IN (?)', array($objectIDs));
		
		$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
			".$conditionsBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionsBuilder->getParameters());
	}
	
	/**
	 * Returns all tags set for given object.
	 * 
	 * @param	string			$objectType
	 * @param	integer			$objectID
	 * @param	array<integer>		$languageIDs
	 * @return	array<wcf\data\tag\Tag>
	 */
	public function getObjectTags($objectType, $objectID, array $languageIDs = array()) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		// get tags
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("tag_to_object.objectTypeID = ?", array($objectTypeID));
		$conditions->add("tag_to_object.objectID = ?", array($objectID));
		if (!empty($languageIDs)) {
			foreach ($languageIDs as $index => $languageID) {
				if (!$languageID) unset($languageIDs[$index]);
			}
			
			if (!empty($languageIDs)) {
				$conditions->add("tag_to_object.languageID IN (?)", array($languageIDs));
			}
		}
		
		$sql = "SELECT		tag.*
			FROM		wcf".WCF_N."_tag_to_object tag_to_object
			LEFT JOIN	wcf".WCF_N."_tag tag
			ON		(tag.tagID = tag_to_object.tagID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$tags = array();
		
		while ($row = $statement->fetchArray()) {
			$tags[$row['tagID']] = new Tag(null, $row);
		}
		
		return $tags;
	}
	
	/**
	 * Returns id of the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		// get object type
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' is not valid for definition 'com.woltlab.wcf.tagging.taggableObject'");
		}
		
		return $objectTypeObj->objectTypeID;
	}
}
