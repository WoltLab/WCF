<?php

namespace wcf\page;

use wcf\system\request\LinkHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Shows a list of unread articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class UnreadArticleListPage extends ArticleListPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    public $controllerName = 'UnreadArticleList';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getLink(
            'UnreadArticleList',
            $this->controllerParameters,
            ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : '')
        );
    }

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add(
            'article.time > ?',
            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
        );

        if (WCF::getUser()->userID) {
            $this->objectList->sqlConditionJoins = "
                LEFT JOIN   wcf1_tracked_visit tracked_visit
                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                        AND tracked_visit.objectID = article.articleID
                        AND tracked_visit.userID = " . WCF::getUser()->userID;
            $this->objectList->getConditionBuilder()->add("(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
        }
    }
}
