<?php
namespace wcf\system\moderation\queue\activation;
use wcf\data\comment\CommentAction;
use wcf\data\moderation\queue\ModerationQueue;
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
class CommentResponseModerationQueueActivationHandler extends AbstractCommentResponseModerationQueueHandler implements IModerationQueueActivationHandler {
	/**
	 * @inheritDoc
	 */
	public function enableContent(ModerationQueue $queue) {
		if ($this->isValid($queue->objectID) && $this->getResponse($queue->objectID)->isDisabled) {
			$response = $this->getResponse($queue->objectID);
			
			$commentAction = new CommentAction([$this->getComment($response->commentID)], 'enableResponse', [
				'responses' => [$response]
			]);
			$commentAction->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		return $this->getRelatedContent($queue);
	}
}
