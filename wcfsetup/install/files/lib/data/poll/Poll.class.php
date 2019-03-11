<?php
namespace wcf\data\poll;
use wcf\data\poll\option\PollOption;
use wcf\data\DatabaseObject;
use wcf\data\IPollObject;
use wcf\system\poll\PollManager;
use wcf\system\WCF;

/**
 * Represents a poll.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Poll
 *
 * @property-read	integer		$pollID			unique id of the poll
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.poll` object type
 * @property-read	integer		$objectID		id of the poll container object the poll belongs to
 * @property-read	string		$question		question of the poll
 * @property-read	integer		$time			timestamp at which the poll has been created
 * @property-read	integer		$endTime		timestamp at which the poll has been/will be closed
 * @property-read	integer		$isChangeable		is `1` if participants can change their vote, otherwise `0`
 * @property-read	integer		$isPublic		is `1` if the result of the poll is public, otherwise `0`
 * @property-read	integer		$sortByVotes		is `1` if the results will be sorted by votes, otherwise `0`
 * @property-read	integer		$resultsRequireVote	is `1` if a user has to have voted to see the results, otherwise `0`
 * @property-read	integer		$maxVotes		maximum number of options the user can select
 * @property-read	integer		$votes			number of votes in the poll
 */
class Poll extends DatabaseObject {
	/**
	 * participation status
	 * @var	boolean
	 */
	protected $isParticipant = false;
	
	/**
	 * list of poll options
	 * @var	PollOption[]
	 */
	protected $options = [];
	
	/**
	 * related object
	 * @var	IPollObject
	 */
	protected $relatedObject = null;
	
	/**
	 * Adds an option to current poll.
	 * 
	 * @param	PollOption	$option
	 */
	public function addOption(PollOption $option) {
		if ($option->pollID == $this->pollID) {
			$this->options[$option->optionID] = $option;
			
			/** @noinspection PhpUndefinedFieldInspection */
			if ($option->voted) {
				$this->isParticipant = true;
			}
		}
	}
	
	/**
	 * Returns a list of poll options.
	 * 
	 * @param	boolean		$isResultDisplay
	 * @return	PollOption[]
	 */
	public function getOptions($isResultDisplay = false) {
		$this->loadOptions();
		
		if ($isResultDisplay && $this->sortByVotes) {
			uasort($this->options, function($a, $b) {
				if ($a->votes == $b->votes) {
					return 0;
				}
				
				return ($a->votes > $b->votes) ? -1 : 1;
			});
		}
		else {
			// order options by show order
			uasort($this->options, function($a, $b) {
				return ($a->showOrder < $b->showOrder) ? -1 : 1;
			});
		}
		
		return $this->options;
	}
	
	/**
	 * Returns true if current user is a participant.
	 * 
	 * @return	boolean
	 */
	public function isParticipant() {
		$this->loadOptions();
		
		return $this->isParticipant;
	}
	
	/**
	 * Loads associated options.
	 */
	protected function loadOptions() {
		if (!empty($this->options)) {
			return;
		}
		
		$optionList = PollManager::getInstance()->getPollOptions([$this->pollID]);
		foreach ($optionList as $option) {
			$this->options[$option->optionID] = $option;
			
			/** @noinspection PhpUndefinedFieldInspection */
			if ($option->voted) {
				$this->isParticipant = true;
			}
		}
	}
	
	/**
	 * Returns true if poll is already finished.
	 * 
	 * @return	boolean
	 */
	public function isFinished() {
		return ($this->endTime && $this->endTime <= TIME_NOW);
	}
	
	/**
	 * Returns true if current user can vote.
	 * 
	 * @return	boolean
	 */
	public function canVote() {
		// guest voting is not possible
		if (!WCF::getUser()->userID) {
			return false;
		}
		else if ($this->isFinished()) {
			return false;
		}
		else if ($this->isParticipant() && !$this->isChangeable) {
			return false;
		}
		
		if ($this->objectID) {
			// related object required but not given, deny vote ability
			if ($this->relatedObject === null) {
				return false;
			}
			
			// validate permissions
			if (!$this->relatedObject->canVote()) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns true if current user can see the result.
	 * 
	 * @return	boolean
	 */
	public function canSeeResult() {
		if ($this->isFinished() || $this->isParticipant() || !$this->resultsRequireVote) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if current user can view the participant list.
	 * 
	 * @return	boolean
	 */
	public function canViewParticipants() {
		if ($this->canSeeResult() && $this->isPublic) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sets related object for this poll.
	 * 
	 * @param	IPollObject	$object
	 */
	public function setRelatedObject(IPollObject $object) {
		$this->relatedObject = $object;
	}
	
	/**
	 * Returns related object.
	 * 
	 * @return	IPollObject
	 */
	public function getRelatedObject() {
		return $this->relatedObject;
	}
}
