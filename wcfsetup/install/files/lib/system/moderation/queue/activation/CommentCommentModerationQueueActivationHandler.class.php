<?php
namespace wcf\system\moderation\queue\activation;
use wcf\data\comment\CommentAction;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\AbstractCommentCommentModerationQueueHandler;

/**
 * An implementation of IModerationQueueActivationHandler for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
class CommentCommentModerationQueueActivationHandler extends AbstractCommentCommentModerationQueueHandler implements IModerationQueueActivationHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.activation';
	
	/**
	 * @inheritDoc
	 */
	public function enableContent(ModerationQueue $queue) {
		if ($this->isValid($queue->objectID) && $this->getComment($queue->objectID)->isDisabled) {
			$commentAction = new CommentAction([$this->getComment($queue->objectID)], 'enable');
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
