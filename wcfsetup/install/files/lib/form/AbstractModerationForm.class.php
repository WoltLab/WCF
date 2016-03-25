<?php
namespace wcf\form;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\comment\CommentHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Provides an abstract form for moderation queue processing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractModerationForm extends AbstractForm {
	/**
	 * id of the assigned user
	 * @var	integer
	 */
	public $assignedUserID = 0;
	
	/**
	 * data used for moderation queue update
	 * @var	array
	 */
	public $data = [];
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.general.canUseModeration'];
	
	/**
	 * moderation queue object
	 * @var	ViewableModerationQueue
	 */
	public $queue = null;
	
	/**
	 * queue id
	 * @var	integer
	 */
	public $queueID = 0;
	
	/**
	 * comment object type id
	 * @var	integer
	 */
	public $commentObjectTypeID = 0;
	
	/**
	 * comment manager object
	 * @var	ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * list of comments
	 * @var	StructuredCommentList
	 */
	public $commentList = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->queueID = intval($_REQUEST['id']);
		$this->queue = ViewableModerationQueue::getViewableModerationQueue($this->queueID);
		if ($this->queue === null) {
			throw new IllegalLinkException();
		}
		
		if (!$this->queue->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->assignedUserID = $this->queue->assignedUserID;
		}
		
		WCF::getBreadcrumbs()->add(new Breadcrumb(
			WCF::getLanguage()->get('wcf.moderation.moderation'),
			LinkHandler::getInstance()->getLink('ModerationList')
		));
		
		$this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue');
		$this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
		$this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->queueID);
		
		// update queue visit
		if ($this->queue->isNew()) {
			$action = new ModerationQueueAction([$this->queue->getDecoratedObject()], 'markAsRead', [
				'visitTime' => TIME_NOW
			]);
			$action->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'assignedUserID' => $this->assignedUserID,
			'queue' => $this->queue,
			'queueID' => $this->queueID,
			'commentCanAdd' => true,
			'commentList' => $this->commentList,
			'commentObjectTypeID' => $this->commentObjectTypeID,
			'lastCommentTime' => ($this->commentList ? $this->commentList->getMinCommentTime() : 0),
			'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltlab.wcf.ModerationForm'),
			'sidebarName' => 'com.woltlab.wcf.ModerationForm'
		]);
	}
	
	/**
	 * Prepares update of moderation queue item.
	 */
	protected function prepareSave() {
		EventHandler::getInstance()->fireAction($this, 'prepareSave');
	}
}
