<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\search\mysql\MysqlSearchIndexManager;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Manages the search index.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class SearchIndexManager extends SingletonFactory implements ISearchIndexManager {
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = [];
	
	/**
	 * list of application packages
	 * @var	Package[]
	 */
	protected static $packages = [];
	
	/**
	 * search index manager object
	 * @var	ISearchIndexManager
	 */
	protected $searchIndexManager = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
	}
	
	/**
	 * Returns the id of the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 * @throws	SystemException
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Returns the the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	ObjectType
	 * @throws	SystemException
	 */
	public function getObjectType($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType];
	}
	
	/**
	 * Returns the search index manager object.
	 * 
	 * @return	ISearchIndexManager
	 */
	protected function getSearchIndexManager() {
		if ($this->searchIndexManager === null) {
			$className = '';
			if (SEARCH_ENGINE != 'mysql') {
				$className = 'wcf\system\search\\'.SEARCH_ENGINE.'\\'.ucfirst(SEARCH_ENGINE).'SearchIndexManager';
				if (!class_exists($className)) {
					$className = '';
				}
			}
			
			// fallback to MySQL
			if (empty($className)) {
				$className = MysqlSearchIndexManager::class;
			}
			
			$this->searchIndexManager = call_user_func([$className, 'getInstance']);
		}
		
		return $this->searchIndexManager;
	}
	
	/**
	 * @inheritDoc
	 */
	public function set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		// strip html; remove whitespace from beginning and end of the message
		$message = StringUtil::trim(StringUtil::stripHTML($message));
		
		$this->getSearchIndexManager()->set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @inheritDoc
	 * @deprecated  3.0 - please use `->set()` which takes the same parameters.
	 */
	public function add($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		$this->set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @inheritDoc
	 * @deprecated  3.0 - please use `->set()` which takes the same parameters.
	 */
	public function update($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID = null, $metaData = '') {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		$this->set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete($objectType, array $objectIDs) {
		$this->getSearchIndexManager()->delete($objectType, $objectIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset($objectType) {
		$this->getSearchIndexManager()->reset($objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createSearchIndices() {
		$this->getSearchIndexManager()->createSearchIndices();
	}
	
	/**
	 * @inheritDoc
	 */
	public function beginBulkOperation() {
		$this->getSearchIndexManager()->beginBulkOperation();
	}
	
	/**
	 * @inheritDoc
	 */
	public function commitBulkOperation() {
		$this->getSearchIndexManager()->commitBulkOperation();
	}
	
	/**
	 * Returns the database table name for the object type's search index.
	 * 
	 * @param	mixed		$objectType
	 * @return	string
	 */
	public static function getTableName($objectType) {
		if (is_string($objectType)) {
			$objectType = self::getInstance()->getObjectType($objectType);
		}
		
		if ($objectType->searchindex) {
			$tableName = $objectType->searchindex;
			
			if (!empty($tableName)) {
				return ApplicationHandler::insertRealDatabaseTableNames($tableName, true);
			}
		}
		
		return 'wcf'.WCF_N.'_search_index_'.substr(sha1($objectType->objectType), 0, 8);
	}
}
