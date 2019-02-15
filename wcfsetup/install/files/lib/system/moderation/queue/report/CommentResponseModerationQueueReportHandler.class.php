<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentResponseModerationQueueHandler;

/**
 * An implementation of IModerationQueueReportHandler for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
class CommentResponseModerationQueueReportHandler extends AbstractCommentResponseModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @inheritDoc
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		$response = $this->getResponse($objectID);
		$comment = $this->getComment($response->commentID);
		if (!$this->getCommentManager($comment)->isAccessible($comment->objectID)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		return $this->getRelatedContent($queue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedObject($objectID) {
		return $this->getResponse($objectID);
	}
}
