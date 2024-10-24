<?php

namespace wcf\system\box;

use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @property    UserTrophyList $objectList
 */
class UserTrophyListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    public $defaultLimit = 10;

    /**
     * @inheritDoc
     */
    public $maximumLimit = 50;

    /**
     * @inheritDoc
     */
    public $minimumLimit = 3;

    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'sidebarLeft',
        'sidebarRight',
        'contentTop',
        'contentBottom',
        'top',
        'bottom',
    ];

    /**
     * @inheritDoc
     */
    public $sortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $sortField = 'time';

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.userTrophyList.condition';

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        $list = new UserTrophyList();

        if (!empty($list->sqlJoins)) {
            $list->sqlJoins .= ' ';
        }
        if (!empty($list->sqlConditionJoins)) {
            $list->sqlConditionJoins .= ' ';
        }
        $list->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID';
        $list->sqlConditionJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID';

        // trophy category join
        $list->sqlJoins .= '
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';
        $list->sqlConditionJoins .= '
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $list->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $list->getConditionBuilder()->add('category.isDisabled = ?', [0]);

        $canViewTrophiesOptionID = User::getUserOptionID('canViewTrophies');
        if (!WCF::getUser()->userID) {
            $list->getConditionBuilder()->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   userOption' . $canViewTrophiesOptionID . ' = 0
            )');
        } elseif (!WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
            $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
            $conditionBuilder->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   (
                            userOption' . $canViewTrophiesOptionID . ' = 0
                         OR userOption' . $canViewTrophiesOptionID . ' = 1
                        )
            )');

            $friendshipConditionBuilder = new PreparedStatementConditionBuilder(false);
            $friendshipConditionBuilder->add('user_trophy.userID IN (
                SELECT  userID
                FROM    wcf1_user_option_value
                WHERE   userOption' . $canViewTrophiesOptionID . ' = 2
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

            $list->getConditionBuilder()->add('(' . $conditionBuilder . ')', $conditionBuilder->getParameters());
        }

        return $list;
    }

    /**
     * @inheritDoc
     */
    public function getTemplate()
    {
        $userIDs = [];

        foreach ($this->objectList->getObjects() as $trophy) {
            $userIDs[] = $trophy->userID;
        }

        UserProfileRuntimeCache::getInstance()->cacheObjectIDs(\array_unique($userIDs));

        return WCF::getTPL()->fetch('boxUserTrophyList', 'wcf', [
            'boxUserTrophyList' => $this->objectList,
            'boxPosition' => $this->box->position,
        ], true);
    }
}
