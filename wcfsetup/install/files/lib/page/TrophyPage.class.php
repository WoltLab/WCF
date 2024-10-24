<?php

namespace wcf\page;

use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\MetaTagHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a trophy page.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @property    UserTrophyList $objectList
 */
class TrophyPage extends MultipleLinkPage
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
    public $objectListClassName = UserTrophyList::class;

    /**
     * @inheritDoc
     */
    public $sortField = 'time';

    /**
     * @inheritDoc
     */
    public $sortOrder = 'DESC';

    /**
     * the trophy id
     * @var int
     */
    public $trophyID = 0;

    /**
     * The trophy instance
     * @var Trophy
     */
    public $trophy;

    /**
     * category object
     * @var TrophyCategory
     */
    public $category;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        PageLocationManager::getInstance()->addParentLocation(
            'com.woltlab.wcf.TrophyCategoryList',
            $this->trophy->getCategory()->getObjectID(),
            $this->trophy->getCategory()
        );

        // Add meta tags.
        MetaTagHandler::getInstance()->addTag(
            'og:title',
            'og:title',
            $this->trophy->getTitle() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE),
            true
        );
        MetaTagHandler::getInstance()->addTag('og:url', 'og:url', $this->trophy->getLink(), true);

        if ($this->trophy->getDescription()) {
            MetaTagHandler::getInstance()->addTag(
                'og:description',
                'og:description',
                StringUtil::decodeHTML(StringUtil::stripHTML($this->trophy->getDescription())),
                true
            );
        }

        if ($this->trophy->type == Trophy::TYPE_IMAGE) {
            MetaTagHandler::getInstance()->addTag(
                'og:image',
                'og:image',
                WCF::getPath() . 'images/trophy/' . $this->trophy->iconFile,
                true
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->trophyID = \intval($_REQUEST['id']);
        }

        $this->trophy = TrophyCache::getInstance()->getTrophyByID($this->trophyID);
        if ($this->trophy === null) {
            throw new IllegalLinkException();
        }

        if ($this->trophy->isDisabled()) {
            throw new PermissionDeniedException();
        }

        $this->category = $this->trophy->getCategory();

        $this->canonicalURL = LinkHandler::getInstance()->getLink('Trophy', [
            'object' => $this->trophy,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $userIDs = [];
        /** @var UserTrophy $trophy */
        foreach ($this->objectList->getObjects() as $trophy) {
            $userIDs[] = $trophy->userID;
        }

        UserProfileRuntimeCache::getInstance()->cacheObjectIDs(\array_unique($userIDs));
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add('user_trophy.trophyID = ?', [$this->trophy->getObjectID()]);
        $canViewTrophyDefaultValue = UserOptionCacheBuilder::getInstance()->getData()['options']['canViewTrophies']->defaultValue;
        $canViewTrophiesOptionID = User::getUserOptionID('canViewTrophies');

        if (!WCF::getUser()->userID) {
            $this->objectList->getConditionBuilder()->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   COALESCE(userOption' . $canViewTrophiesOptionID . ', ' . $canViewTrophyDefaultValue . ') = 0)');
        } elseif (!WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
            $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
            $conditionBuilder->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   (
                            COALESCE(userOption' . $canViewTrophiesOptionID . ', ' . $canViewTrophyDefaultValue . ') = 0
                         OR COALESCE(userOption' . $canViewTrophiesOptionID . ', ' . $canViewTrophyDefaultValue . ') = 1
                        )
            )');

            $friendshipConditionBuilder = new PreparedStatementConditionBuilder(false);
            $friendshipConditionBuilder->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   COALESCE(userOption' . $canViewTrophiesOptionID . ', ' . $canViewTrophyDefaultValue . ') = 2
            )');
            $friendshipConditionBuilder->add(
                'user_trophy.userID IN (
                    SELECT  userID
                    FROM    wcf1_user_follow
                    WHERE   followUserID = ?
                )',
                [WCF::getUser()->userID]
            );
            $conditionBuilder->add(
                '(' . $friendshipConditionBuilder . ')',
                $friendshipConditionBuilder->getParameters()
            );
            $conditionBuilder->add('user_trophy.userID = ?', [WCF::getUser()->userID]);

            $this->objectList->getConditionBuilder()->add(
                '(' . $conditionBuilder . ')',
                $conditionBuilder->getParameters()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'trophy' => $this->trophy,
            'trophyID' => $this->trophyID,
        ]);

        if (\count($this->objectList) === 0) {
            @\header('HTTP/1.1 404 Not Found');
        }
    }
}
