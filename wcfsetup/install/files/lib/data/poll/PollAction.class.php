<?php
namespace wcf\data\poll;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\option\PollOptionEditor;
use wcf\data\poll\option\PollOptionList;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes poll-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.poll
 * @category	Community Framework
 * 
 * @method	PollEditor[]	getObjects()
 * @method	PollEditor	getSingleObject()
 */
class PollAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getGroupedUserList'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = PollEditor::class;
	
	/**
	 * poll object
	 * @var	\wcf\data\poll\Poll
	 */
	protected $poll = null;
	
	/**
	 * @inheritDoc
	 * @return	Poll
	 */
	public function create() {
		if (!isset($this->parameters['data']['time'])) $this->parameters['data']['time'] = TIME_NOW;
		
		/** @var Poll $poll */
		$poll = parent::create();
		
		// create options
		$sql = "INSERT INTO	wcf".WCF_N."_poll_option
					(pollID, optionValue, showOrder)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['options'] as $showOrder => $option) {
			$statement->execute([
				$poll->pollID,
				$option['optionValue'],
				$showOrder
			]);
		}
		WCF::getDB()->commitTransaction();
		
		return $poll;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		// read current poll
		$pollEditor = reset($this->objects);
		
		// get current options
		$optionList = new PollOptionList();
		$optionList->getConditionBuilder()->add("poll_option.pollID = ?", [$pollEditor->pollID]);
		$optionList->sqlOrderBy = "poll_option.showOrder ASC";
		$optionList->readObjects();
		$options = $optionList->getObjects();
		
		$newOptions = $updateOptions = [];
		foreach ($this->parameters['options'] as $showOrder => $option) {
			// check if editing an existing option
			if ($option['optionID']) {
				// check if an update is required
				if ($options[$option['optionID']]->showOrder != $showOrder || $options[$option['optionID']]->optionValue != $option['optionValue']) {
					$updateOptions[$option['optionID']] = [
						'optionValue' => $option['optionValue'],
						'showOrder' => $showOrder
					];
				}
				
				// remove option
				unset($options[$option['optionID']]);
			}
			else {
				$newOptions[] = [
					'optionValue' => $option['optionValue'],
					'showOrder' => $showOrder
				];
			}
		}
		
		if (!empty($newOptions) || !empty($updateOptions) || !empty($options)) {
			WCF::getDB()->beginTransaction();
			
			// check if new options should be created
			if (!empty($newOptions)) {
				$sql = "INSERT INTO	wcf".WCF_N."_poll_option
							(pollID, optionValue, showOrder)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				foreach ($newOptions as $option) {
					$statement->execute([
						$pollEditor->pollID,
						$option['optionValue'],
						$option['showOrder']
					]);
				}
			}
			
			// check if existing options should be updated
			if (!empty($updateOptions)) {
				$sql = "UPDATE	wcf".WCF_N."_poll_option
					SET	optionValue = ?,
						showOrder = ?
					WHERE	optionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				foreach ($updateOptions as $optionID => $option) {
					$statement->execute([
						$option['optionValue'],
						$option['showOrder'],
						$optionID
					]);
				}
			}
			
			// check if options should be removed
			if (!empty($options)) {
				$sql = "DELETE FROM	wcf".WCF_N."_poll_option
					WHERE		optionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				foreach ($options as $option) {
					$statement->execute([$option->optionID]);
				}
			}
			
			// force recalculation of poll stats
			$pollEditor->calculateVotes();
			
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Executes a user's vote.
	 */
	public function vote() {
		$poll = current($this->objects);
		
		// get previous vote
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_poll_option_vote
			WHERE	pollID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$poll->pollID,
			WCF::getUser()->userID
		]);
		$alreadyVoted = false;
		$optionIDs = [];
		while ($row = $statement->fetchArray()) {
			$alreadyVoted = true;
			$optionIDs[] = $row['optionID'];
		}
		
		// calculate the difference
		foreach ($this->parameters['optionIDs'] as $index => $optionID) {
			$optionsIndex = array_search($optionID, $optionIDs);
			if ($optionsIndex !== false) {
				// ignore this option
				unset($this->parameters['optionIDs'][$index]);
				unset($optionIDs[$optionsIndex]);
			}
		}
		
		// insert new vote options
		if (!empty($this->parameters['optionIDs'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_option_vote
						(pollID, optionID, userID)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->parameters['optionIDs'] as $optionID) {
				$statement->execute([
					$poll->pollID,
					$optionID,
					WCF::getUser()->userID
				]);
			}
			
			// increase votes per option
			$sql = "UPDATE	wcf".WCF_N."_poll_option
				SET	votes = votes + 1
				WHERE	optionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->parameters['optionIDs'] as $optionID) {
				$statement->execute([$optionID]);
			}
		}
		
		// remove previous options
		if (!empty($optionIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_poll_option_vote
				WHERE		optionID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($optionIDs as $optionID) {
				$statement->execute([
					$optionID,
					WCF::getUser()->userID
				]);
			}
			
			// decrease votes per option
			$sql = "UPDATE	wcf".WCF_N."_poll_option
				SET	votes = votes - 1
				WHERE	optionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($optionIDs as $optionID) {
				$statement->execute([$optionID]);
			}
		}
		
		// increase poll votes
		if (!$alreadyVoted) {
			$poll->increaseVotes();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateGetGroupedUserList() {
		$this->readInteger('pollID');
		
		// read poll
		$this->poll = new Poll($this->parameters['pollID']);
		if (!$this->poll->pollID) {
			throw new UserInputException('pollID');
		}
		else if (!$this->poll->canViewParticipants()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getGroupedUserList() {
		// get options
		$sql = "SELECT		optionID, optionValue
			FROM		wcf".WCF_N."_poll_option
			WHERE		pollID = ?
			ORDER BY	".($this->poll->sortByVotes ? "votes DESC" : "showOrder ASC");
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->poll->pollID]);
		$options = [];
		while ($row = $statement->fetchArray()) {
			$options[$row['optionID']] = new GroupedUserList($row['optionValue'], 'wcf.poll.noVotes');
		}
		
		// get votes
		$sql = "SELECT	userID, optionID
			FROM	wcf".WCF_N."_poll_option_vote
			WHERE	pollID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->poll->pollID]);
		$voteData = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($voteData[$row['optionID']])) {
				$voteData[$row['optionID']] = [];
			}
			
			$voteData[$row['optionID']][] = $row['userID'];
		}
		
		// assign user ids
		foreach ($voteData as $optionID => $userIDs) {
			$options[$optionID]->addUserIDs($userIDs);
		}
		
		// load user profiles
		GroupedUserList::loadUsers();
		
		WCF::getTPL()->assign([
			'groupedUsers' => $options
		]);
		
		return [
			'pageCount' => 1,
			'template' => WCF::getTPL()->fetch('groupedUserList')
		];
	}
	
	/**
	 * Copies a poll from one object id to another.
	 */
	public function copy() {
		$sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.poll', $this->parameters['sourceObjectType']);
		$targetObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.poll', $this->parameters['targetObjectType']);
		
		//
		// step 1) get data
		//
		
		// get poll
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_poll
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$sourceObjectType->objectTypeID,
			$this->parameters['sourceObjectID']
		]);
		$row = $statement->fetchArray();
		
		if ($row === false) {
			return [
				'pollID' => null
			];
		}
		
		// get options
		$pollOptionList = new PollOptionList();
		$pollOptionList->getConditionBuilder()->add("poll_option.pollID = ?", [$row['pollID']]);
		$pollOptionList->readObjects();
		
		//
		// step 2) copy
		//
		
		// cretae poll
		$pollData = $row;
		$pollData['objectTypeID'] = $targetObjectType->objectTypeID;
		$pollData['objectID'] = $this->parameters['targetObjectID'];
		unset($pollData['pollID']);
		
		$newPoll = PollEditor::create($pollData);
		
		// create options
		$newOptionIDs = [];
		foreach ($pollOptionList as $pollOption) {
			$newOption = PollOptionEditor::create([
				'pollID' => $newPoll->pollID,
				'optionValue' => $pollOption->optionValue,
				'votes' => $pollOption->votes,
				'showOrder' => $pollOption->showOrder
			]);
			
			$newOptionIDs[$pollOption->optionID] = $newOption->optionID;
		}
		
		// copy votes
		WCF::getDB()->beginTransaction();
		foreach ($newOptionIDs as $oldOptionID => $newOptionID) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_option_vote
						(pollID, optionID, userID)
				SELECT		".$newPoll->pollID.", ".$newOptionID.", userID
				FROM		wcf".WCF_N."_poll_option_vote
				WHERE		optionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$oldOptionID]);
		}
		WCF::getDB()->commitTransaction();
		
		return [
			'pollID' => $newPoll->pollID
		];
	}
}
