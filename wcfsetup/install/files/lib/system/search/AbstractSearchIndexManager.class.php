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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
abstract class AbstractSearchIndexManager extends SingletonFactory implements ISearchIndexManager {
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::createSearchIndices()
	 */
	public function createSearchIndices() {
		// get definition id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('com.woltlab.wcf.searchableObjectType'));
		$row = $statement->fetchArray();
		
		$objectTypeList = new ObjectTypeList();
		$objectTypeList->getConditionBuilder()->add("object_type.definitionID = ?", array($row['definitionID']));
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
	 * @see	\wcf\system\search\ISearchIndexManager::beginBulkOperation()
	 */
	public function beginBulkOperation() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\search\ISearchIndexManager::commitBulkOperation()
	 */
	public function commitBulkOperation() {
		// does nothing
	}
}
