<?php
namespace wcf\system\poll;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\option\PollOptionList;
use wcf\data\poll\Poll;
use wcf\data\poll\PollAction;
use wcf\data\poll\PollList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides methods to create and manage polls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.poll
 * @category	Community Framework
 */
class PollManager extends SingletonFactory {
	/**
	 * list of object types
	 * @var	ObjectType[]
	 */
	protected $cache = [];
	
	/**
	 * current object id
	 * @var	integer
	 */
	protected $objectID = 0;
	
	/**
	 * current object type
	 * @var	string
	 */
	protected $objectType = '';
	
	/**
	 * poll object
	 * @var	\wcf\data\poll\Poll
	 */
	protected $poll = null;
	
	/**
	 * poll data
	 * @var	mixed[]
	 */
	protected $pollData = [
		'endTime' => '',
		'isChangeable' => 0,
		'isPublic' => 0,
		'maxVotes' => 1,
		'question' => '',
		'resultsRequireVote' => 0,
		'sortByVotes' => 0
	];
	
	/**
	 * poll id
	 * @var	integer
	 */
	protected $pollID = 0;
	
	/**
	 * list of poll options
	 * @var	string[]
	 */
	protected $pollOptions = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.poll');
		foreach ($objectTypes as $objectType) {
			$this->cache[$objectType->objectType] = $objectType;
		}
	}
	
	/**
	 * Removes a list of polls by id.
	 * 
	 * @param	integer[]		$pollIDs
	 */
	public function removePolls(array $pollIDs) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("pollID IN (?)", [$pollIDs]);
		
		$sql = "DELETE FROM	wcf".WCF_N."_poll
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
	
	/**
	 * Sets object data.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	integer		$pollID
	 * @return	boolean
	 * @throws	SystemException
	 */
	public function setObject($objectType, $objectID, $pollID = 0) {
		if (!isset($this->cache[$objectType])) {
			throw new SystemException("Object type '".$objectType."' is unknown");
		}
		
		$this->objectID = intval($objectID);
		$this->objectType = $objectType;
		$this->pollID = $pollID;
		
		// load poll
		if ($this->pollID) {
			$this->poll = new Poll($this->pollID);
			if (!$this->poll->pollID) {
				$this->poll = null;
				$this->pollID = 0;
				
				return false;
			}
			
			// populate poll data
			$this->pollData = [
				'endTime' => $this->poll->endTime,
				'isChangeable' => $this->poll->isChangeable,
				'isPublic' => $this->poll->isPublic,
				'maxVotes' => $this->poll->maxVotes,
				'question' => $this->poll->question,
				'resultsRequireVote' => $this->poll->resultsRequireVote,
				'sortByVotes' => $this->poll->sortByVotes
			];
			
			// load poll options
			$sql = "SELECT		optionID, optionValue
				FROM		wcf".WCF_N."_poll_option
				WHERE		pollID = ?
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->poll->pollID]);
			while ($row = $statement->fetchArray()) {
				$this->pollOptions[] = $row;
			}
		}
		
		return true;
	}
	
	/**
	 * Reads form parameters for polls.
	 */
	public function readFormParameters() {
		// reset poll data and options prior to reading form input
		$this->pollData = $this->pollOptions = [];
		
		// poll data
		if (isset($_POST['pollEndTime'])) {
			$d = \DateTime::createFromFormat('Y-m-d H:i', $_POST['pollEndTime'], WCF::getUser()->getTimeZone());
			$this->pollData['endTime'] = ($d !== false) ? $d->getTimestamp() : 0;
		}
		
		if (isset($_POST['pollMaxVotes'])) $this->pollData['maxVotes'] = max(intval($_POST['pollMaxVotes']), 1); // force a minimum of 1
		if (isset($_POST['pollQuestion'])) $this->pollData['question'] = StringUtil::trim($_POST['pollQuestion']);
		
		// boolean values
		$this->pollData['isChangeable'] = (isset($_POST['pollIsChangeable'])) ? 1 : 0;
		$this->pollData['resultsRequireVote'] = (isset($_POST['pollResultsRequireVote'])) ? 1 : 0;
		$this->pollData['sortByVotes'] = (isset($_POST['pollSortByVotes'])) ? 1 : 0;
		
		if ($this->poll === null) {
			$this->pollData['isPublic'] = (isset($_POST['pollIsPublic']) && $this->canStartPublicPoll()) ? 1 : 0;
		}
		else {
			// visibility cannot be changed after creation
			$this->pollData['isPublic'] = $this->poll->isPublic;
		}
		
		// poll options
		if (isset($_POST['pollOptions']) && is_array($_POST['pollOptions'])) {
			foreach ($_POST['pollOptions'] as $showOrder => $value) {
				list($optionID, $optionValue) = explode('_', $value, 2);
				$this->pollOptions[$showOrder] = [
					'optionID' => intval($optionID),
					'optionValue' => StringUtil::trim($optionValue)
				];
			}
		}
	}
	
	/**
	 * Validates poll parameters.
	 */
	public function validate() {
		if ($this->pollData['endTime'] && $this->pollData['endTime'] <= TIME_NOW) {
			if ($this->poll === null || $this->poll->endTime >= TIME_NOW) {
				// end time is in the past
				throw new UserInputException('pollEndTime', 'notValid');
			}
		}
		
		$count = count($this->pollOptions);
		if (empty($this->pollData['question'])) {
			if ($count) {
				// options given, but no question
				throw new UserInputException('pollQuestion');
			}
			else {
				// if no question and no options are given, ignore poll completely
				return;
			}
		}
		else if (!$count) {
			// no options given
			throw new UserInputException('pollOptions');
		}
		
		// too many options provided, discard superfluous options
		if ($count > POLL_MAX_OPTIONS) {
			$this->pollOptions = array_slice($this->pollOptions, 0, POLL_MAX_OPTIONS);
		}
		
		// less options available than allowed
		if ($count < $this->pollData['maxVotes']) {
			throw new UserInputException('pollMaxVotes', 'notValid');
		}
	}
	
	/**
	 * Handles poll creation, modification and deletion. Returns poll id or zero
	 * if poll was deleted or nothing was created.
	 * 
	 * @param	integer		$objectID
	 * @return	integer
	 * @throws	SystemException
	 */
	public function save($objectID = null) {
		if ($objectID !== null) {
			$this->objectID = intval($objectID);
		}
		
		// create a new poll
		if ($this->poll === null) {
			// no poll should be created
			if (empty($this->pollData['question'])) {
				return 0;
			}
			
			// validate if object type is given
			if (empty($this->objectType)) {
				throw new SystemException("Could not create poll, missing object type");
			}
			
			$data = $this->pollData;
			$data['objectID'] = $this->objectID;
			$data['objectTypeID'] = $this->cache[$this->objectType]->objectTypeID;
			$data['time'] = TIME_NOW;
			
			$action = new PollAction([], 'create', [
				'data' => $data,
				'options' => $this->pollOptions
			]);
			$returnValues = $action->executeAction();
			$this->poll = $returnValues['returnValues'];
		}
		else {
			// remove poll
			if (empty($this->pollData['question'])) {
				$action = new PollAction([$this->poll], 'delete');
				$action->executeAction();
				$this->poll = null;
				
				return 0;
			}
			else {
				// update existing poll
				$action = new PollAction([$this->poll], 'update', [
					'data' => $this->pollData,
					'options' => $this->pollOptions
				]);
				$action->executeAction();
			}
		}
		
		return $this->poll->pollID;
	}
	
	/**
	 * Assigns variables for poll management or display.
	 */
	public function assignVariables() {
		$variables = [
			'__showPoll' => true,
			'pollID' => ($this->poll === null ? 0 : $this->poll->pollID),
			'pollOptions' => $this->pollOptions
		];
		foreach ($this->pollData as $key => $value) {
			if ($key == 'endTime') {
				if (!$value) $value = '';
			}
			
			$key = 'poll'.ucfirst($key);
			$variables[$key] = $value;
		}
		
		WCF::getTPL()->assign($variables);
	}
	
	/**
	 * Returns true if current user can start a public poll.
	 * 
	 * @return	boolean
	 */
	public function canStartPublicPoll() {
		$handler = $this->getHandler(null, $this->objectType);
		if ($handler !== null) {
			return $handler->canStartPublicPoll();
		}
		
		return true;
	}
	
	/**
	 * Returns a list of polls including options and vote state for current user.
	 * 
	 * @param	integer[]		$pollIDs
	 * @return	Poll[]
	 */
	public function getPolls(array $pollIDs) {
		$pollList = new PollList();
		$pollList->setObjectIDs($pollIDs);
		$pollList->readObjects();
		$polls = $pollList->getObjects();
		
		// invalid poll ids
		if (empty($polls)) {
			return [];
		}
		
		// fetch options for every poll
		$optionList = $this->getPollOptions($pollIDs);
		
		// assign options to poll
		foreach ($optionList as $option) {
			$polls[$option->pollID]->addOption($option);
		}
		
		return $polls;
	}
	
	/**
	 * Returns a list of poll options with vote state for current user.
	 * 
	 * @param	integer[]		$pollIDs
	 * @return	\wcf\data\poll\option\PollOptionList
	 */
	public function getPollOptions(array $pollIDs) {
		$optionList = new PollOptionList();
		$optionList->getConditionBuilder()->add("poll_option.pollID IN (?)", [$pollIDs]);
		
		// check for user votes
		if (WCF::getUser()->userID) {
			$optionList->sqlSelects = "CASE WHEN poll_option_vote.optionID IS NULL THEN '0' ELSE '1' END AS voted";
			$optionList->sqlJoins = "LEFT JOIN wcf".WCF_N."_poll_option_vote poll_option_vote ON (poll_option_vote.optionID = poll_option.optionID AND poll_option_vote.userID = ".WCF::getUser()->userID.")";
		}
		else {
			$optionList->sqlSelects = "'0' AS voted";
		}
		
		$optionList->readObjects();
		
		return $optionList;
	}
	
	/**
	 * Returns related object for given poll object.
	 * 
	 * @param	\wcf\data\poll\Poll	$poll
	 * @return	\wcf\data\IPollObject
	 */
	public function getRelatedObject(Poll $poll) {
		if ($poll->objectID) {
			return $this->getHandler($poll->objectTypeID)->getRelatedObject($poll);
		}
		
		return null;
	}
	
	/**
	 * Returns the handler object for given object type. Returns false if object type (id)
	 * is not found, or null if no handler is assigned.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	string		$objectType
	 * @return	mixed
	 * @throws	SystemException
	 */
	protected function getHandler($objectTypeID, $objectType = '') {
		if ($objectTypeID !== null) {
			foreach ($this->cache as $objectTypeObj) {
				if ($objectTypeObj->objectTypeID == $objectTypeID) {
					$objectType = $objectTypeObj->objectType;
					break;
				}
			}
		}
		
		if (!isset($this->cache[$objectType])) {
			throw new SystemException("Object type '".$objectType."' (id ".$objectTypeID.") is not valid for object type definition 'com.woltlab.wcf.poll'");
		}
		
		if ($this->cache[$objectType]->className === null) {
			throw new SystemException("Object type '".$objectType."' does not provide a processor class name");
		}
		
		// validates against object type's class
		$className = $this->cache[$objectType]->className;
		if (!is_subclass_of($className, IPollHandler::class)) {
			throw new ImplementationException($className, IPollHandler::class);
		}
		else if (!is_subclass_of($className, SingletonFactory::class)) {
			throw new ParentClassException($className, SingletonFactory::class);
		}
		
		$object = call_user_func([$className, 'getInstance']);
		return $object;
	}
}
