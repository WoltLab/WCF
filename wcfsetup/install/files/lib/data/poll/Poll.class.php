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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.poll
 * @subpackage	data.poll
 * @category	Community Framework
 */
class Poll extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'poll';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'pollID';
	
	/**
	 * participation status
	 * @var	boolean
	 */
	protected $isParticipant = false;
	
	/**
	 * list of poll options
	 * @var	array<wcf\data\poll\option\PollOption>
	 */
	protected $options = array();
	
	/**
	 * related object
	 * @var	wcf\data\IPollObject
	 */
	protected $relatedObject = null;
	
	/**
	 * Adds an option to current poll.
	 * 
	 * @param	wcf\data\poll\option\PollOption		$option
	 */
	public function addOption(PollOption $option) {
		if ($option->pollID == $this->pollID) {
			$this->options[$option->optionID] = $option;
			
			if ($option->voted) {
				$this->isParticipant = true;
			}
		}
	}
	
	/**
	 * Returns a list of poll options.
	 * 
	 * @param	boolean		$isResultDisplay
	 * @return	array<wcf\data\poll\option\PollOption>
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
		
		$optionList = PollManager::getInstance()->getPollOptions(array($this->pollID));
		foreach ($optionList as $option) {
			$this->options[$option->optionID] = $option;
			
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
		if ($this->endTime >= TIME_NOW) {
			return true;
		}
		
		return false;
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
	 * Sets related object for this poll.
	 * 
	 * @param	wcf\data\IPollObject		$object
	 */
	public function setRelatedObject(IPollObject $object) {
		$this->relatedObject = $object;
	}
	
	/**
	 * Returns related object.
	 * 
	 * @return	wcf\data\IPollObject
	 */
	public function getRelatedObject() {
		return $this->relatedObject;
	}
}
