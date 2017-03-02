<?php
namespace wcf\system\worker;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\WCF;

/**
 * Worker implementation for updating comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class CommentRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $limit = 500;
	
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		if ($this->count === null) {
			$this->count = 0;
			$sql = "SELECT	MAX(commentID) AS commentID
				FROM	wcf".WCF_N."_comment";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$row = $statement->fetchArray();
			if ($row !== false) $this->count = $row['commentID'];
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CommentList();
		$this->objectList->sqlOrderBy = 'comment.commentID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->objectList->getConditionBuilder()->add('comment.commentID BETWEEN ? AND ?', [$this->limit * $this->loopCount + 1, $this->limit * $this->loopCount + $this->limit]);
		
		parent::execute();
		
		if (!count($this->objectList)) {
			return;
		}
		
		WCF::getDB()->beginTransaction();
		/** @var Comment $comment */
		foreach ($this->objectList as $comment) {
			// update message
			if (!$comment->enableHtml) {
				$this->getHtmlInputProcessor()->process($comment->message, 'com.woltlab.wcf.comment', $comment->commentID, true);
				
				(new CommentEditor($comment))->update([
					'message' => $this->getHtmlInputProcessor()->getHtml(),
					'enableHtml' => 1
				]);
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}
		
		return $this->htmlInputProcessor;
	}
}
