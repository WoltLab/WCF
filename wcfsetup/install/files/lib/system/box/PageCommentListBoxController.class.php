<?php
declare(strict_types=1);
namespace wcf\system\box;
use wcf\system\comment\CommentHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for the comments of the active page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 */
class PageCommentListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['contentTop', 'contentBottom'];
	
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
		/** @noinspection PhpUndefinedMethodInspection */
		return WCF::getTPL()->fetch('boxPageComments', 'wcf', [
			'commentCanAdd' => WCF::getSession()->getPermission('user.page.canAddComment'),
			'commentList' => $this->objectList,
			'commentObjectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page'),
			'lastCommentTime' => $this->objectList->getMinCommentTime(),
			'pageID' => RequestHandler::getInstance()->getActiveRequest()->getPageID(),
			'likeData' => (MODULE_LIKE && $this->objectList) ? $this->objectList->getLikeData() : []
		], true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		return RequestHandler::getInstance()->getActiveRequest() && (WCF::getSession()->getPermission('user.page.canAddComment') || parent::hasContent());
	}
}
