<?php
namespace wcf\system\worker;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\WCF;

/**
 * Worker implementation for updating comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class CommentResponseRebuildDataWorker extends AbstractRebuildDataWorker {
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
			$sql = "SELECT	MAX(responseID) AS responseID
				FROM	wcf".WCF_N."_comment_response";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$row = $statement->fetchArray();
			if ($row !== false) $this->count = $row['responseID'];
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CommentResponseList();
		$this->objectList->sqlOrderBy = 'comment_response.responseID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->objectList->getConditionBuilder()->add('comment_response.responseID BETWEEN ? AND ?', [$this->limit * $this->loopCount + 1, $this->limit * $this->loopCount + $this->limit]);
		
		parent::execute();
		
		if (!count($this->objectList)) {
			return;
		}
		
		// retrieve permissions
		$userIDs = [];
		foreach ($this->objectList as $response) {
			$userIDs[] = $response->userID;
		}
		$userPermissions = $this->getBulkUserPermissions($userIDs, ['user.comment.disallowedBBCodes']);
		
		WCF::getDB()->beginTransaction();
		/** @var CommentResponse $response */
		foreach ($this->objectList as $response) {
			$responseEditor = new CommentResponseEditor($response);
			
			BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', $this->getBulkUserPermissionValue($userPermissions, $response->userID, 'user.comment.disallowedBBCodes')));
			
			// update message
			if (!$response->enableHtml) {
				$this->getHtmlInputProcessor()->process($response->message, 'com.woltlab.wcf.comment.response', $response->responseID, true);
				
				$responseEditor->update([
					'message' => $this->getHtmlInputProcessor()->getHtml(),
					'enableHtml' => 1
				]);
			}
			else {
				$this->getHtmlInputProcessor()->reprocess($response->message, 'com.woltlab.wcf.comment.response', $response->responseID);
				$responseEditor->update(['message' => $this->getHtmlInputProcessor()->getHtml()]);
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
