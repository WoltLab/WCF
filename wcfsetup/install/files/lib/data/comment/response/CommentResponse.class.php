<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\comment\CommentHandler;
use wcf\util\StringUtil;

/**
 * Represents a comment response.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class CommentResponse extends DatabaseObject implements IMessage {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'comment_response';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'responseID';
	
	/**
	 * comment object
	 * @var	\wcf\data\comment\Comment
	 */
	protected $comment = null;
	
	/**
	 * @see	\wcf\data\IMessage::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		return SimpleMessageParser::getInstance()->parse($this->message);
	}
	
	/**
	 * Returns comment object related to this response.
	 * 
	 * @return	\wcf\data\comment\Comment
	 */
	public function getComment() {
		if ($this->comment === null) {
			$this->comment = new Comment($this->commentID);
		}
		
		return $this->comment;
	}
	
	/**
	 * Sets related comment object.
	 * 
	 * @param	\wcf\data\comment\Comment
	 */
	public function setComment(Comment $comment) {
		if ($this->commentID == $comment->commentID) {
			$this->comment = $comment;
		}
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
		return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getLink($this->getComment()->objectTypeID, $this->getComment()->objectID);
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getTitle($this->getComment()->objectTypeID, $this->getComment()->objectID, true);
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
