<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Default implementation for moderation queue handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
abstract class AbstractModerationQueueHandler implements IModerationQueueHandler {
	/**
	 * database object class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * definition name
	 * @var	string
	 */
	protected $definitionName = '';
	
	/**
	 * object type
	 * @var	string
	 */
	protected $objectType = '';
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::identifyOrphans()
	 */
	public function identifyOrphans(array $queues) {
		if (empty($this->className) || !class_exists($this->className) || !ClassUtil::isInstanceOf($this->className, 'wcf\data\DatabaseObject')) {
			throw new SystemException("DatabaseObject class name '" . $this->className . "' is missing or invalid");
		}
		
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		$tableName = call_user_func(array($this->className, 'getDatabaseTableName'));
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add($indexName . " IN (?)", array(array_keys($queues)));
		
		$sql = "SELECT	" . $indexName . "
			FROM	" . $tableName . "
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			unset($queues[$row[$indexName]]);
		}
		
		return array_values($queues);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::removeQueues()
	 */
	public function removeQueues(array $objectIDs) {
		$objectTypeID = ModerationQueueManager::getInstance()->getObjectTypeID($this->definitionName, $this->objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Object type '".$this->objectType."' is not valid for definition '".$this->definitionName."'");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_moderation_queue
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$queueIDs = array();
		while ($row = $statement->fetchArray()) {
			$queueIDs[] = $row['queueID'];
		}
		
		if (!empty($queueIDs)) {
			$queueAction = new ModerationQueueAction($queueIDs, 'delete');
			$queueAction->executeAction();
		}
	}
}
