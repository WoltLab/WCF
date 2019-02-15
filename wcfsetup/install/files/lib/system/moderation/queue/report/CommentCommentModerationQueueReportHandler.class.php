<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentCommentModerationQueueHandler;

/**
 * An implementation of IModerationQueueReportHandler for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
class CommentCommentModerationQueueReportHandler extends AbstractCommentCommentModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';
	
	/**
	 * @inheritDoc
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		$comment = $this->getComment($objectID);
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
		return $this->getComment($objectID);
	}
}
