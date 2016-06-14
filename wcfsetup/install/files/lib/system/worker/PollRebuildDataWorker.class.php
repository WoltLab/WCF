<?php
namespace wcf\system\worker;
use wcf\data\poll\PollList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Worker implementation for updating polls.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class PollRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = PollList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 10;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'poll.pollID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$pollIDs = [];
		foreach ($this->getObjectList() as $poll) {
			$pollIDs[] = $poll->pollID;
		}
		
		if (!empty($pollIDs)) {
			// update poll options
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('poll_option.pollID IN (?)', [$pollIDs]);
			$sql = "UPDATE	wcf" . WCF_N . "_poll_option poll_option
				SET	votes = (
						SELECT	COUNT(*)
						FROM	wcf" . WCF_N . "_poll_option_vote
						WHERE	optionID = poll_option.optionID
					)
					" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			
			// update polls
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('poll.pollID IN (?)', [$pollIDs]);
			$sql = "UPDATE	wcf" . WCF_N . "_poll poll
				SET	votes = (
						SELECT	COUNT(*)
						FROM	wcf" . WCF_N . "_poll_option_vote
						WHERE	pollID = poll.pollID
					)
					" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
		}
	}
}
