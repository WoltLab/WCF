<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\comment\CommentHandler;
use wcf\util\StringUtil;

/**
 * Represents a comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 *
 * @property-read	integer		$commentID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer		$time
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	string		$message
 * @property-read	integer		$responses
 * @property-read	string		$responseIDs
 */
class Comment extends DatabaseObject implements IMessage {
	use TUserContent;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'comment';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'commentID';
	
	/**
	 * Returns a list of response ids.
	 * 
	 * @return	integer[]
	 */
	public function getResponseIDs() {
		if ($this->responseIDs === null || $this->responseIDs == '') {
			return [];
		}
		
		$responseIDs = @unserialize($this->responseIDs);
		if ($responseIDs === false) {
			return [];
		}
		
		return $responseIDs;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return SimpleMessageParser::getInstance()->parse($this->message);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getExcerpt($maxLength = 255) {
		return StringUtil::truncateHTML($this->getFormattedMessage(), $maxLength);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getLink($this->objectTypeID, $this->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getTitle($this->objectTypeID, $this->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}
}
