<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\comment\CommentHandler;
use wcf\util\StringUtil;

/**
 * Represents a comment response.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment\Response
 *
 * @property-read	integer		$responseID
 * @property-read	integer		$commentID
 * @property-read	integer		$time
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	string		$message
 */
class CommentResponse extends DatabaseObject implements IMessage {
	use TUserContent;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'comment_response';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'responseID';
	
	/**
	 * comment object
	 * @var	Comment
	 */
	protected $comment = null;
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return SimpleMessageParser::getInstance()->parse($this->message);
	}
	
	/**
	 * Returns comment object related to this response.
	 * 
	 * @return	Comment
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
	 * @param	Comment		$comment
	 */
	public function setComment(Comment $comment) {
		if ($this->commentID == $comment->commentID) {
			$this->comment = $comment;
		}
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
		return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getLink($this->getComment()->objectTypeID, $this->getComment()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getTitle($this->getComment()->objectTypeID, $this->getComment()->objectID, true);
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
