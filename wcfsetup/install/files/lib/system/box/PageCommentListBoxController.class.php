<?php
namespace wcf\system\box;
use wcf\system\comment\CommentHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for the comments of the active page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class PageCommentListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['contentTop', 'contentBottom'];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page');
		$commentManager = CommentHandler::getInstance()->getObjectType($commentObjectTypeID)->getProcessor();
		
		return CommentHandler::getInstance()->getCommentList($commentManager, $commentObjectTypeID, RequestHandler::getInstance()->getActiveRequest()->getPageID(), false);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxPageComments', 'wcf', [
			'commentCanAdd' => WCF::getSession()->getPermission('user.pageComment.canAddComment'),
			'commentList' => $this->objectList,
			'commentObjectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page'),
			'lastCommentTime' => $this->objectList->getMinCommentTime(),
			'pageID' => RequestHandler::getInstance()->getActiveRequest()->getPageID()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		return WCF::getSession()->getPermission('user.pageComment.canAddComment') || parent::hasContent();
	}
}
