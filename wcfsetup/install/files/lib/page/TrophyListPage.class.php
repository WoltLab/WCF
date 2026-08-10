<?php

namespace wcf\page;

use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\TrophyList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a trophy page.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends MultipleLinkPage<TrophyList>
 */
class TrophyListPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.trophy.canSeeTrophies'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 30;

    /**
     * @inheritDoc
     */
    public $objectListClassName = TrophyList::class;

    /**
     * selected sort field
     * @var string
     */
    public $sortField = 'trophy.showOrder';

    /**
     * selected sort order
     * @var string
     */
    public $sortOrder = 'ASC';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getControllerLink(
            TrophyListPage::class,
            [],
            ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : '')
        );

        if (\count(TrophyCategoryCache::getInstance()->getEnabledCategories()) === 0) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = '(
            SELECT  COUNT(*)
            FROM    wcf1_user_trophy
            WHERE   trophyID = trophy.trophyID
        ) AS awarded';
        $this->objectList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $this->objectList->getConditionBuilder()->add('categoryID IN (?)', [
            \array_map(static function ($category) {
                return $category->categoryID;
            }, TrophyCategoryCache::getInstance()->getEnabledCategories()),
        ]);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categories' => TrophyCategoryCache::getInstance()->getEnabledCategories(),
        ]);

        if (\count($this->objectList) === 0) {
            @\header('HTTP/1.1 404 Not Found');
        }
    }
}
