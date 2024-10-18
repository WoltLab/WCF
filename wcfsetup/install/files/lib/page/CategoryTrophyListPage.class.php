<?php

namespace wcf\page;

use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\TrophyList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a trophy page.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 *
 * @property    TrophyList $objectList
 */
class CategoryTrophyListPage extends TrophyListPage
{
    /**
     * the category id filter
     * @var int
     */
    public $categoryID = 0;

    /**
     * The category object filter
     * @var TrophyCategory
     */
    public $category;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }

        $this->category = TrophyCategoryCache::getInstance()->getCategoryByID($this->categoryID);

        if (!$this->category) {
            throw new IllegalLinkException();
        }

        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }

        $this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryTrophyList', [
            'object' => $this->category,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        MultipleLinkPage::initObjectList();

        $this->objectList->sqlSelects = '(
            SELECT  COUNT(*)
            FROM    wcf1_user_trophy
            WHERE   trophyID = trophy.trophyID
        ) AS awarded';
        $this->objectList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $this->objectList->getConditionBuilder()->add('categoryID = ?', [$this->categoryID]);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'category' => $this->category,
            'categoryID' => $this->categoryID,
        ]);
    }
}
