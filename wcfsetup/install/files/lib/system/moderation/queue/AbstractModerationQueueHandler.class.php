<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Default implementation for moderation queue handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.moderation
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
abstract class AbstractModerationQueueHandler implements IModerationQueueHandler {
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
	 * @see	wcf\system\moderation\queue\IModerationQueueHandler::removeQueues()
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
