<?php
namespace wcf\system\box;
use wcf\system\comment\CommentHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for page comments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class PageCommentsBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['contentTop', 'contentBottom'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.page.comments');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page');
		$commentManager = CommentHandler::getInstance()->getObjectType($commentObjectTypeID)->getProcessor();
		$commentList = CommentHandler::getInstance()->getCommentList($commentManager, $commentObjectTypeID, RequestHandler::getInstance()->getActiveRequest()->getPageID());
		
		if (WCF::getSession()->getPermission('user.pageComment.canAddComment') || count($commentList)) {
			WCF::getTPL()->assign([
				'pageID' => RequestHandler::getInstance()->getActiveRequest()->getPageID(),
				'commentCanAdd' => WCF::getSession()->getPermission('user.pageComment.canAddComment'),
				'commentList' => $commentList,
				'commentObjectTypeID' => $commentObjectTypeID,
				'lastCommentTime' => $commentList->getMinCommentTime()
			]);
			
			$this->content = WCF::getTPL()->fetch('boxPageComments');
		}	
	}
}
