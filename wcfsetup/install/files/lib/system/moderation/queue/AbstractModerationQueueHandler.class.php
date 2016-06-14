<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * Default implementation for moderation queue handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
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
	 * required permission for assigned users
	 * @var	string
	 */
	protected $requiredPermission = 'mod.general.canUseModeration';
	
	/**
	 * @inheritDoc
	 */
	public function identifyOrphans(array $queues) {
		if (empty($this->className) || !class_exists($this->className) || !is_subclass_of($this->className, DatabaseObject::class)) {
			throw new SystemException("DatabaseObject class name '" . $this->className . "' is missing or invalid");
		}
		
		$indexName = call_user_func([$this->className, 'getDatabaseTableIndexName']);
		$tableName = call_user_func([$this->className, 'getDatabaseTableName']);
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add($indexName . " IN (?)", [array_keys($queues)]);
		
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
	 * @inheritDoc
	 */
	public function removeQueues(array $objectIDs) {
		$objectTypeID = ModerationQueueManager::getInstance()->getObjectTypeID($this->definitionName, $this->objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Object type '".$this->objectType."' is not valid for definition '".$this->definitionName."'");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [$objectTypeID]);
		$conditions->add("objectID IN (?)", [$objectIDs]);
		
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_moderation_queue
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$queueIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($queueIDs)) {
			$queueAction = new ModerationQueueAction($queueIDs, 'delete');
			$queueAction->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function canRemoveContent(ModerationQueue $queue) {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAffectedUser(ModerationQueue $queue, $userID) {
		$user = new UserProfile(new User($userID));
		return $user->getPermission($this->requiredPermission);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.0
	 */
	public function getCommentNotificationLanguageItemPrefix() {
		// this implementation exists to provide backwards compatibility;
		// as there are no abstract implementations of the two interfaces,
		// this is the best approach
		if ($this instanceof IModerationQueueActivationHandler) {
			return 'wcf.moderation.activation.notification';
		}
		else if ($this instanceof IModerationQueueReportHandler) {
			return 'wcf.moderation.report.notification';
		}
		
		return 'wcf.moderation.notification';
	}
}
