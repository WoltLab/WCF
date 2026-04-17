<?php

namespace wcf\system\box;

use wcf\data\comment\StructuredCommentList;
use wcf\system\comment\CommentHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for the comments of the active page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectListBoxController<StructuredCommentList>
 */
class PageCommentListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['contentTop', 'contentBottom'];

    #[\Override]
    protected function getObjectList()
    {
        $commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page');
        $commentManager = CommentHandler::getInstance()->getObjectType($commentObjectTypeID)->getProcessor();

        return CommentHandler::getInstance()->getCommentList(
            $commentManager,
            $commentObjectTypeID,
            RequestHandler::getInstance()->getActivePageID() ?: 0,
            false
        );
    }

    #[\Override]
    protected function getTemplate()
    {
        return WCF::getTPL()->render('wcf', 'boxPageComments', [
            'commentCanAdd' => WCF::getSession()->hasPermission('user.page.canAddComment'),
            'commentList' => $this->objectList,
            'commentObjectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page'),
            'lastCommentTime' => $this->objectList->getMinCommentTime(),
            'pageID' => RequestHandler::getInstance()->getActivePageID() ?: 0,
            'likeData' => (\MODULE_LIKE) ? $this->objectList->getLikeData() : [],
        ]);
    }

    #[\Override]
    public function hasContent()
    {
        return RequestHandler::getInstance()->getActiveRequest() && (WCF::getSession()->hasPermission('user.page.canAddComment') || parent::hasContent());
    }
}
