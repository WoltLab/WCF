<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\comment\CommentHandler;
use wcf\util\StringUtil;

/**
 * Represents a comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class Comment extends DatabaseObject implements IMessage {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'comment';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'commentID';
	
	/**
	 * Returns a list of response ids.
	 * 
	 * @return	array<integer>
	 */
	public function getResponseIDs() {
		if ($this->responseIDs === null || $this->responseIDs == '') {
			return array();
		}
		
		$responseIDs = @unserialize($this->responseIDs);
		if ($responseIDs === false) {
			return array();
		}
		
		return $responseIDs;
	}
	
	/**
	 * @see	\wcf\data\IMessage::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		return SimpleMessageParser::getInstance()->parse($this->message);
	}
	
	/**
	 * @see	\wcf\data\IMessage::getExcerpt()
	 */
	public function getExcerpt($maxLength = 255) {
		return StringUtil::truncateHTML($this->getFormattedMessage(), $maxLength);
	}
	
	/**
	 * @see	\wcf\data\IMessage::getMessage()
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @see	\wcf\data\IUserContent::getTime()
	 */
	public function getTime() {
		return $this->time;
	}
	
	/**
	 * @see	\wcf\data\IUserContent::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @see	\wcf\data\IUserContent::getUsername()
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * @see	\wcf\data\ILinkableObject::getLink()
	 */
	public function getLink() {
		return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getLink($this->objectTypeID, $this->objectID);
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getTitle($this->objectTypeID, $this->objectID);
	}
	
	/**
	 * @see	\wcf\data\IMessage::isVisible()
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @see	\wcf\data\IMessage::__toString()
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}
}
