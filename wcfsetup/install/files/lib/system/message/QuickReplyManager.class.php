<?php
namespace wcf\system\message;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IAttachmentMessageQuickReplyAction;
use wcf\data\IMessage;
use wcf\data\IMessageQuickReplyAction;
use wcf\system\bbcode\PreParser;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\ClassUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Manages quick replies and stored messages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message
 * @category	Community Framework
 */
class QuickReplyManager extends SingletonFactory {
	/**
	 * list of allowed bbcodes
	 * @var	array<string>
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
		WCF::getSession()->register('quickReply-'.$type.'-'.$objectID, $message);
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
	 * @param	array<string>		$allowedBBCodes
	 */
	public function setAllowedBBCodes(array $allowedBBCodes = null) {
		$this->allowedBBodes = $allowedBBCodes;
	}
	
	/**
	 * Validates parameters for current request.
	 * 
	 * @param	\wcf\system\message\IMessageQuickReplyAction	$object
	 * @param	array<array>					$parameters
	 * @param	string						$containerClassName
	 * @param	string						$containerDecoratorClassName
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
			if (!ClassUtil::isInstanceOf($containerDecoratorClassName, 'wcf\data\DatabaseObjectDecorator')) {
				throw new SystemException("'".$containerDecoratorClassName."' does not extend 'wcf\data\DatabaseObjectDecorator'");
			}
			
			$this->container = new $containerDecoratorClassName($this->container);
		}
		$object->validateContainer($this->container);
		
		// validate message
		$object->validateMessage($this->container, $parameters['data']['message']);
		
		// check for message quote ids
		$parameters['removeQuoteIDs'] = (isset($parameters['removeQuoteIDs']) && is_array($parameters['removeQuoteIDs'])) ? ArrayUtil::trim($parameters['removeQuoteIDs']) : array();
		
		// check for tmp hash (attachments)
		$parameters['tmpHash'] = '';
		if (isset($parameters['data']['tmpHash'])) {
			$parameters['tmpHash'] = StringUtil::trim($parameters['data']['tmpHash']);
			unset($parameters['data']['tmpHash']);
		}
		
		// message settings
		$parameters['data'] = array_merge($parameters['data'], MessageFormSettingsHandler::getSettings($parameters));
		
		$parameters['data']['enableHtml'] = 0;
		$parameters['data']['showSignature'] = (WCF::getUser()->userID ? WCF::getUser()->showSignature : 0);
		
		EventHandler::getInstance()->fireAction($this, 'validateParameters', $parameters);
	}
	
	/**
	 * Creates a new message and returns the parsed template.
	 * 
	 * @param	\wcf\data\IMessageQuickReplyAction	$object
	 * @param	array<array>				$parameters
	 * @param	string					$containerActionClassName
	 * @param	string					$sortOrder
	 * @param	string					$templateName
	 * @param	string					$application
	 * @return	array
	 */
	public function createMessage(IMessageQuickReplyAction $object, array &$parameters, $containerActionClassName, $sortOrder, $templateName, $application = 'wcf') {
		$additionalFields = array();
		EventHandler::getInstance()->fireAction($this, 'createMessage', $additionalFields);
		
		$tableIndexName = call_user_func(array($this->container, 'getDatabaseTableIndexName'));
		$parameters['data'][$tableIndexName] = $parameters['objectID'];
		$parameters['data']['time'] = TIME_NOW;
		$parameters['data']['userID'] = WCF::getUser()->userID ?: null;
		$parameters['data']['username'] = WCF::getUser()->username;
		
		// pre-parse message text
		if ($parameters['data']['preParse']) {
			$parameters['data']['message'] = PreParser::getInstance()->parse($parameters['data']['message'], $this->allowedBBodes);
		}
		unset($parameters['data']['preParse']);
		
		$parameters['data'] = array_merge($additionalFields, $parameters['data']);
		
		// attachment support
		if (MODULE_ATTACHMENT && !empty($parameters['tmpHash']) && $object instanceof IAttachmentMessageQuickReplyAction) {
			$parameters['attachmentHandler'] = $object->getAttachmentHandler($this->container);
		}
		
		$message = $object->create();
		$eventParameters = array('message' => $message);
		EventHandler::getInstance()->fireAction($this, 'createdMessage', $eventParameters);
		
		if ($message instanceof IMessage && !$message->isVisible()) {
			return array(
				'isVisible' => false
			);
		}
		
		// resolve the page no
		list($pageNo, $count) = $object->getPageNo($this->container);
		
		// we're still on current page
		if ($pageNo == $parameters['pageNo']) {
			// check for additional messages
			$messageList = $object->getMessageList($this->container, $parameters['lastPostTime']);
				
			// calculate start index
			$startIndex = $count - (count($messageList) - 1);
			
			WCF::getTPL()->assign(array(
				'attachmentList' => $messageList->getAttachmentList(),
				'container' => $this->container,
				'objects' => $messageList,
				'startIndex' => $startIndex,
				'sortOrder' => $sortOrder,
			));
			
			// assign 'to top' link
			if (isset($parameters['anchor'])) {
				WCF::getTPL()->assign('anchor', $parameters['anchor']);
			}
			
			// update visit time (messages shouldn't occur as new upon next visit)
			if (ClassUtil::isInstanceOf($containerActionClassName, 'wcf\data\IVisitableObjectAction')) {
				$containerAction = new $containerActionClassName(array(($this->container instanceof DatabaseObjectDecorator ? $this->container->getDecoratedObject() : $this->container)), 'markAsRead');
				$containerAction->executeAction();
			}
			
			return array(
				'lastPostTime' => $message->time,
				'template' => WCF::getTPL()->fetch($templateName, $application)
			);
		}
		else {
			// redirect
			return array(
				'url' => $object->getRedirectUrl($this->container, $message)
			);
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
