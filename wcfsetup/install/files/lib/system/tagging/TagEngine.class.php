<?php
namespace wcf\system\tagging;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\data\tag\TagList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\language\LanguageFactory;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the tagging of objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
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
			$statement->execute([
				$objectTypeID,
				$objectID,
				$languageID
			]);
		}
		
		// get tag ids
		$tagIDs = [];
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
				$tagAction = new TagAction([], 'create', ['data' => [
					'name' => $tag,
					'languageID' => $languageID
				]]);
				
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
			VALUES		(?, ?, ?, ?)";
		WCF::getDB()->beginTransaction();
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($tagIDs as $tagID) {
			$statement->execute([$objectID, $tagID, $objectTypeID, $languageID]);
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
		$parameters = [
			$objectTypeID,
			$objectID
		];
		if ($languageID !== null) $parameters[] = $languageID;
		$statement->execute($parameters);
	}
	
	/**
	 * Deletes all tags assigned to given tagged objects.
	 * 
	 * @param	string			$objectType
	 * @param	integer[]		$objectIDs
	 */
	public function deleteObjects($objectType, array $objectIDs) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$conditionsBuilder = new PreparedStatementConditionBuilder();
		$conditionsBuilder->add('objectTypeID = ?', [$objectTypeID]);
		$conditionsBuilder->add('objectID IN (?)', [$objectIDs]);
		
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
	 * @param	integer[]		$languageIDs
	 * @return	Tag[]
	 */
	public function getObjectTags($objectType, $objectID, array $languageIDs = []) {
		$tags = $this->getObjectsTags($objectType, [$objectID], $languageIDs);
		
		return isset($tags[$objectID]) ? $tags[$objectID] : [];
	}
	
	/**
	 * Returns all tags set for given objects.
	 * 
	 * @param	string			$objectType
	 * @param	integer[]		$objectIDs
	 * @param	integer[]		$languageIDs
	 * @return	array
	 */
	public function getObjectsTags($objectType, array $objectIDs, array $languageIDs = []) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("tag_to_object.objectTypeID = ?", [$objectTypeID]);
		$conditions->add("tag_to_object.objectID IN (?)", [$objectIDs]);
		if (!empty($languageIDs)) {
			foreach ($languageIDs as $index => $languageID) {
				if (!$languageID) unset($languageIDs[$index]);
			}
			
			if (!empty($languageIDs)) {
				$conditions->add("tag_to_object.languageID IN (?)", [$languageIDs]);
			}
		}
		
		$sql = "SELECT		tag.*, tag_to_object.objectID
			FROM		wcf".WCF_N."_tag_to_object tag_to_object
			LEFT JOIN	wcf".WCF_N."_tag tag
			ON		(tag.tagID = tag_to_object.tagID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$tags = [];
		while ($tag = $statement->fetchObject(Tag::class)) {
			/** @noinspection PhpUndefinedFieldInspection */
			$objectID = $tag->objectID;
			if (!isset($tags[$objectID])) {
				$tags[$objectID] = [];
			}
			$tags[$objectID][$tag->tagID] = $tag;
		}
		
		return $tags;
	}
	
	/**
	 * Returns id of the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 * @throws	InvalidObjectTypeException
	 */
	public function getObjectTypeID($objectType) {
		// get object type
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $objectType);
		if ($objectTypeObj === null) {
			throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.tagging.taggableObject');
		}
		
		return $objectTypeObj->objectTypeID;
	}
	
	/**
	 * Returns the implicit language id based on the language id of existing tags.
	 * 
	 * NULL indicates that there are no tags, otherwise the language id with the most
	 * associated tags for that object is returned, but can still be arbitrary if
	 * there are two or more top language ids with the same amount of tags.
	 * 
	 * @param       string          $objectType
	 * @param       integer         $objectID
	 * @return      integer|null
	 */
	public function getImplicitLanguageID($objectType, $objectID) {
		$existingTags = $this->getObjectTags($objectType, $objectID);
		if (empty($existingTags)) {
			return null;
		}
		
		$languageIDs = [];
		foreach ($existingTags as $tag) {
			if (!isset($languageIDs[$tag->languageID])) $languageIDs[$tag->languageID] = 0;
			$languageIDs[$tag->languageID]++;
		}
		
		arsort($languageIDs, SORT_NUMERIC);
		
		return key($languageIDs);
	}
	
	/**
	 * @param Tag[] $tags
	 * @return int[]
	 */
	public function getTagIDs($tags) {
		return array_map(function($tag) {
			return $tag->tagID;
		}, $tags);
	}
	
	/**
	 * Generates the inner SQL statement to fetch object ids that have all listed
	 * tags assigned to them.
	 * 
	 * @param string $objectType
	 * @param Tag[] $tags
	 * @return array
	 * @since 3.2
	 */
	public function getSubselectForObjectsByTags($objectType, array $tags) {
		$parameters = [$this->getObjectTypeID($objectType)];
		$tagIDs = implode(',', array_map(function(Tag $tag) use (&$parameters) {
			$parameters[] = $tag->tagID;
			
			return '?';
		}, $tags));
		$parameters[] = count($tags);
		
		$sql = "SELECT          objectID
			FROM            wcf".WCF_N."_tag_to_object
			WHERE           objectTypeID = ?
					AND tagID IN (".$tagIDs.")
			GROUP BY        objectID
			HAVING          COUNT(objectID) = ?";
		
		return [
			'sql' => $sql,
			'parameters' => $parameters,
		];
	}
	
	public function setJoinCondition($objectType, array $tags, PreparedStatementConditionBuilder $conditions) {
		$conditions->add('tag_to_object.objectTypeID = ?', [$this->getObjectTypeID($objectType)]);
		
		$tagIDs = [];
		foreach ($tags as $tag) {
			$tagIDs[] = $tag->tagID;
		}
		$conditions->add('tag_to_object.tagID IN (?)', [$tagIDs]);
	}
	
	public function getSqlGroupAndHaving(array $tags) {
		return 'GROUP BY tag_to_object.objectID HAVING COUNT(tag_to_object.objectID) = ' . count($tags);
	}
	
	/**
	 * Returns the matching tags by name.
	 * 
	 * @param string[] $names
	 * @param int $languageID
	 * @return Tag[]
	 * @since 3.2
	 */
	public function getTagsByName(array $names, $languageID) {
		$tagList = new TagList();
		$tagList->getConditionBuilder()->add('name IN (?)', [$names]);
		$tagList->getConditionBuilder()->add('languageID = ?', [$languageID ?: WCF::getLanguage()->languageID]);
		$tagList->readObjects();
		
		return $tagList->getObjects();
	}
}
