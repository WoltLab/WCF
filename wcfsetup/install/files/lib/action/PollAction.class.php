<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\poll\PollManager;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles poll interaction.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.poll
 * @subpackage	action
 * @category	Community Framework
 */
class PollAction extends AJAXProxyAction {
	/**
	 * @see	wcf\action\AbstractAction::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * list of option ids
	 * @var	array<integer>
	 */
	public $optionIDs = array();
	
	/**
	 * poll object
	 * @var	wcf\data\poll\Poll
	 */
	public $poll = null;
	
	/**
	 * poll id
	 * @var	integer
	 */
	public $pollID = 0;
	
	/**
	 * related poll object
	 * @var	wcf\data\IPollObject
	 */
	public $relatedObject = null;
	
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		if (!MODULE_POLL) {
			throw new IllegalLinkException();
		}
		
		AbstractSecureAction::readParameters();
		
		if (isset($_POST['actionName'])) $this->actionName = StringUtil::trim($_POST['actionName']);
		if (isset($_POST['pollID'])) $this->pollID = intval($_POST['pollID']);
		
		$polls = PollManager::getInstance()->getPolls(array($this->pollID));
		if (!isset($polls[$this->pollID])) {
			throw new UserInputException('pollID');
		}
		$this->poll = $polls[$this->pollID];
		
		// load related object
		$this->relatedObject = PollManager::getInstance()->getRelatedObject($this->poll);
		if ($this->relatedObject === null) {
			if ($this->poll->objectID) {
				throw new SystemException("Missing related object for poll id '".$this->poll->pollID."'");
			}
		}
		else {
			$this->poll->setRelatedObject($this->relatedObject);
		}
		
		// validate action
		switch ($this->actionName) {
			case 'getResult':
				if (!$this->poll->canSeeResult()) {
					throw new PermissionDeniedException();
				}
			break;
			
			case 'getVote':
			case 'vote':
				if (!$this->poll->canVote()) {
					throw new PermissionDeniedException();
				}
			break;
			
			default:
				throw new SystemException("Unknown action '".$this->actionName."'");
			break;
		}
		
		if (isset($_POST['optionIDs']) && is_array($_POST['optionIDs'])) {
			$this->optionIDs = ArrayUtil::toIntegerArray($_POST['optionIDs']);
			if (count($this->optionIDs) > $this->poll->maxVotes) {
				throw new PermissionDeniedException();
			}
			
			$optionIDs = array();
			foreach ($this->poll->getOptions() as $option) {
				$optionIDs[] = $option->optionID;
			}
			
			foreach ($this->optionIDs as $optionID) {
				if (!in_array($optionID, $optionIDs)) {
					throw new PermissionDeniedException();
				}
			}
		}
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		AbstractAction::execute();
		
		$returnValues = array(
			'actionName' => $this->actionName,
			'pollID' => $this->pollID
		);
		
		switch ($this->actionName) {
			case 'getResult':
				$this->getResult($returnValues);
			break;
			
			case 'getVote':
				$this->getVote($returnValues);
			break;
			
			case 'vote':
				$this->vote($returnValues);
			break;
		}
		
		$this->executed();
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode($returnValues);
		exit;
	}
	
	/**
	 * Renders the result template.
	 */
	public function getResult(array &$returnValues) {
		WCF::getTPL()->assign(array(
			'poll' => $this->poll
		));
		
		$returnValues['resultTemplate'] = WCF::getTPL()->fetch('pollResult');
	}
	
	/**
	 * Renders the vote template.
	 */
	public function getVote(array &$returnValues) {
		WCF::getTPL()->assign(array(
			'poll' => $this->poll
		));
		
		$returnValues['voteTemplate'] = WCF::getTPL()->fetch('pollVote');
	}
	
	/**
	 * Adds a user vote.
	 * 
	 * @param	array<mixed>	$returnValues
	 */
	protected function vote(array &$returnValues) {
		$pollAction = new \wcf\data\poll\PollAction(array($this->poll), 'vote', array('optionIDs' => $this->optionIDs));
		$pollAction->executeAction();
		
		// update poll object
		$polls = PollManager::getInstance()->getPolls(array($this->pollID));
		$this->poll = $polls[$this->pollID];
		if ($this->relatedObject !== null) {
			$this->poll->setRelatedObject($this->relatedObject);
		}
		
		// render result template
		$this->getResult($returnValues);
		
		// render vote template if votes are changeable
		if ($this->poll->isChangeable) {
			$this->getVote($returnValues);
		}
		
		$returnValues['canVote'] = ($this->poll->isChangeable) ? 1 : 0;
	}
}
