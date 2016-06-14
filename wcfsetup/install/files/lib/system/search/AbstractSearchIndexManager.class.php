<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for search index managers, this class should be extended by
 * all search index managers to preserve compatibility in case of interface changes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
abstract class AbstractSearchIndexManager extends SingletonFactory implements ISearchIndexManager {
	/**
	 * @inheritDoc
	 */
	public function set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		$this->add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createSearchIndices() {
		// get definition id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['com.woltlab.wcf.searchableObjectType']);
		$row = $statement->fetchArray();
		
		$objectTypeList = new ObjectTypeList();
		$objectTypeList->getConditionBuilder()->add("object_type.definitionID = ?", [$row['definitionID']]);
		$objectTypeList->readObjects();
		
		foreach ($objectTypeList as $objectType) {
			$this->createSearchIndex($objectType);
		}
	}
	
	/**
	 * Creates the search index for given object type. Returns true if the
	 * index was created, otherwise false.
	 * 
	 * @param	\wcf\data\object\type\ObjectType	$objectType
	 * @return	boolean
	 */
	abstract protected function createSearchIndex(ObjectType $objectType);
	
	/**
	 * @inheritDoc
	 */
	public function beginBulkOperation() {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function commitBulkOperation() {
		// does nothing
	}
}
