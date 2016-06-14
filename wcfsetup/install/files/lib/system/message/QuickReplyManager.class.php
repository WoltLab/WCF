<?php
namespace wcf\system\message;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IAttachmentMessageQuickReplyAction;
use wcf\data\IDatabaseObjectAction;
use wcf\data\IMessage;
use wcf\data\IMessageQuickReplyAction;
use wcf\data\IVisitableObjectAction;
use wcf\system\bbcode\PreParser;
use wcf\system\event\EventHandler;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Manages quick replies and stored messages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message
 */
class QuickReplyManager extends SingletonFactory {
	/**
	 * list of allowed bbcodes
	 * @var	string[]
	 */
	public $allowedBBodes = null;
	
	/**
	 * container object
	 * @var	\wcf\data\DatabaseObject
	 */
	public $container = null;
	
	/**
	 * object id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * object type
	 * @var	string
	 */
	public $type = '';
	
	/**
	 * Returns a stored message from session.
	 * 
	 * @param	string		$type
	 * @param	integer		$objectID
	 * @return	string
	 */
	public function getMessage($type, $objectID) {
		$this->type = $type;
		$this->objectID = $objectID;
		
		// allow manipulation before fetching data
		EventHandler::getInstance()->fireAction($this, 'getMessage');
		
		$message = WCF::getSession()->getVar('quickReply-'.$this->type.'-'.$this->objectID);
		return ($message === null ? '' : $message);
	}
	
	/**
	 * Stores a message in session.
	 * 
	 * @param	string		$type
	 * @param	integer		$objectID
	 * @param	string		$message
	 */
	public function setMessage($type, $objectID, $message) {
		WCF::getSession()->register('quickReply-'.$type.'-'.$objectID, MessageUtil::stripCrap($message));
	}
	
	/**
	 * Removes a stored message from session.
	 * 
	 * @param	string		$type
	 * @param	integer		$objectID
	 */
	public function removeMessage($type, $objectID) {
		WCF::getSession()->unregister('quickReply-'.$type.'-'.$objectID);
	}
	
	/**
	 * Sets the allowed bbcodes.
	 * 
	 * @param	string[]		$allowedBBCodes
	 */
	public function setAllowedBBCodes(array $allowedBBCodes = null) {
		$this->allowedBBodes = $allowedBBCodes;
	}
	
	/**
	 * Validates parameters for current request.
	 * 
	 * @param	IMessageQuickReplyAction	$object
	 * @param	mixed[][]			$parameters
	 * @param	string				$containerClassName
	 * @param	string				$containerDecoratorClassName
	 * @throws	SystemException
	 * @throws	UserInputException
	 */
	public function validateParameters(IMessageQuickReplyAction $object, array &$parameters, $containerClassName, $containerDecoratorClassName = '') {
		if (!isset($parameters['data']['message'])) {
			throw new UserInputException('message');
		}
		
		$parameters['data']['message'] = StringUtil::trim(MessageUtil::stripCrap($parameters['data']['message']));
		
		if (empty($parameters['data']['message'])) {
			throw new UserInputException('message', WCF::getLanguage()->get('wcf.global.form.error.empty'));
		}
		
		$parameters['lastPostTime'] = (isset($parameters['lastPostTime'])) ? intval($parameters['lastPostTime']) : 0;
		if (!$parameters['lastPostTime']) {
			throw new UserInputException('lastPostTime');
		}
		
		$parameters['pageNo'] = (isset($parameters['pageNo'])) ? intval($parameters['pageNo']) : 0;
		if (!$parameters['pageNo']) {
			throw new UserInputException('pageNo');
		}
		
		$parameters['objectID'] = (isset($parameters['objectID'])) ? intval($parameters['objectID']) : 0;
		if (!$parameters['objectID']) {
			throw new UserInputException('objectID');
		}
		
		$this->container = new $containerClassName($parameters['objectID']);
		if (!empty($containerDecoratorClassName)) {
			if (!is_subclass_of($containerDecoratorClassName, DatabaseObjectDecorator::class)) {
				throw new ParentClassException($containerDecoratorClassName, DatabaseObjectDecorator::class);
			}
			
			$this->container = new $containerDecoratorClassName($this->container);
		}
		$object->validateContainer($this->container);
		
		$parameters['htmlInputProcessor'] = $object->getHtmlInputProcessor($parameters['data']['message']);
		unset($parameters['data']['message']);
		
		$parameters['htmlInputProcessor']->validate();
		
		// validate message
		$object->validateMessage($this->container, $parameters['htmlInputProcessor']);
		
		// check for message quote ids
		$parameters['removeQuoteIDs'] = (isset($parameters['removeQuoteIDs']) && is_array($parameters['removeQuoteIDs'])) ? ArrayUtil::trim($parameters['removeQuoteIDs']) : [];
		
		// check for tmp hash (attachments)
		$parameters['tmpHash'] = '';
		if (isset($parameters['data']['tmpHash'])) {
			$parameters['tmpHash'] = StringUtil::trim($parameters['data']['tmpHash']);
			unset($parameters['data']['tmpHash']);
		}
		
		EventHandler::getInstance()->fireAction($this, 'validateParameters', $parameters);
	}
	
	/**
	 * Creates a new message and returns the parsed template.
	 * 
	 * @param	\wcf\data\IMessageQuickReplyAction	$object
	 * @param	array   				$parameters
	 * @param	string					$containerActionClassName
	 * @param	string					$sortOrder
	 * @param	string					$templateName
	 * @param	string					$application
	 * @param       callable                                $callbackCreatedMessage
	 * @return	array
	 */
	public function createMessage(IMessageQuickReplyAction $object, array &$parameters, $containerActionClassName, $sortOrder, $templateName, $application = 'wcf', callable $callbackCreatedMessage = null) {
		$additionalFields = [];
		EventHandler::getInstance()->fireAction($this, 'createMessage', $additionalFields);
		
		$tableIndexName = call_user_func([$this->container, 'getDatabaseTableIndexName']);
		$parameters['data'][$tableIndexName] = $parameters['objectID'];
		$parameters['data']['time'] = TIME_NOW;
		$parameters['data']['userID'] = WCF::getUser()->userID ?: null;
		$parameters['data']['username'] = WCF::getUser()->username;
		
		// pre-parse message text
		/*if ($parameters['data']['preParse']) {
			$parameters['data']['message'] = PreParser::getInstance()->parse($parameters['data']['message'], $this->allowedBBodes);
		}
		unset($parameters['data']['preParse']);*/
		
		$parameters['data'] = array_merge($additionalFields, $parameters['data']);
		
		// attachment support
		if (MODULE_ATTACHMENT && !empty($parameters['tmpHash']) && $object instanceof IAttachmentMessageQuickReplyAction) {
			$parameters['attachmentHandler'] = $object->getAttachmentHandler($this->container);
		}
		
		$message = $object->create();
		$eventParameters = ['message' => $message];
		EventHandler::getInstance()->fireAction($this, 'createdMessage', $eventParameters);
		
		if ($callbackCreatedMessage !== null) {
			$callbackCreatedMessage($message);
		}
		
		if ($message instanceof IMessage && !$message->isVisible()) {
			return ['isVisible' => false];
		}
		
		// resolve the page no
		list($pageNo, $count) = $object->getPageNo($this->container);
		
		// we're still on current page
		if ($pageNo == $parameters['pageNo']) {
			// check for additional messages
			$messageList = $object->getMessageList($this->container, $parameters['lastPostTime']);
			
			// calculate start index
			$startIndex = $count - (count($messageList) - 1);
			
			WCF::getTPL()->assign([
				'attachmentList' => $messageList->getAttachmentList(),
				'container' => $this->container,
				'objects' => $messageList,
				'startIndex' => $startIndex,
				'sortOrder' => $sortOrder
			]);
			
			// assign 'to top' link
			if (isset($parameters['anchor'])) {
				WCF::getTPL()->assign('anchor', $parameters['anchor']);
			}
			
			// update visit time (messages shouldn't occur as new upon next visit)
			if (is_subclass_of($containerActionClassName, IVisitableObjectAction::class)) {
				/** @var IDatabaseObjectAction $containerAction */
				$containerAction = new $containerActionClassName([($this->container instanceof DatabaseObjectDecorator ? $this->container->getDecoratedObject() : $this->container)], 'markAsRead');
				$containerAction->executeAction();
			}
			
			return [
				'lastPostTime' => $message->time,
				'objectID' => $message->getObjectID(),
				'template' => WCF::getTPL()->fetch($templateName, $application)
			];
		}
		else {
			// redirect
			return [
				'objectID' => $message->getObjectID(),
				'url' => $object->getRedirectUrl($this->container, $message)
			];
		}
	}
	
	/**
	 * Returns the container object.
	 * 
	 * @return	\wcf\data\DatabaseObject
	 */
	public function getContainer() {
		return $this->container;
	}
	
	/**
	 * Stores tmpHash in current session, used in combination with the extended form.
	 * 
	 * @param	string		$tmpHash
	 */
	public function setTmpHash($tmpHash) {
		WCF::getSession()->register('__wcfAttachmentTmpHash', $tmpHash);
	}
}
