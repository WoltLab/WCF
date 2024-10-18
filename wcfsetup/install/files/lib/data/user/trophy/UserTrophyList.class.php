<?php

namespace wcf\data\user\trophy;

use wcf\data\DatabaseObjectList;

/**
 * Provides a user trophy list.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @method  UserTrophy      current()
 * @method  UserTrophy[]        getObjects()
 * @method  UserTrophy|null     getSingleObject()
 * @method  UserTrophy|null     search($objectID)
 * @property    UserTrophy[] $objects
 */
class UserTrophyList extends DatabaseObjectList
{
    /**
     * Returns a user trophy list for a certain users.
     *
     * @param int[] $userIDs
     * @param bool $includeDisabled
     * @return  UserTrophy[][]
     */
    public static function getUserTrophies(array $userIDs, $includeDisabled = false)
    {
        if (empty($userIDs)) {
            throw new \InvalidArgumentException('UserIDs cannot be empty.');
        }

        $trophyList = new self();
        $trophyList->getConditionBuilder()->add('user_trophy.userID IN (?)', [$userIDs]);

        if (!$includeDisabled) {
            if (!empty($trophyList->sqlJoins)) {
                $trophyList->sqlJoins .= ' ';
            }
            if (!empty($trophyList->sqlConditionJoins)) {
                $trophyList->sqlConditionJoins .= ' ';
            }
            $trophyList->sqlJoins .= '
                LEFT JOIN   wcf1_trophy trophy
                ON          user_trophy.trophyID = trophy.trophyID';
            $trophyList->sqlConditionJoins .= '
                LEFT JOIN   wcf1_trophy trophy
                ON          user_trophy.trophyID = trophy.trophyID';

            // trophy category join
            $trophyList->sqlJoins .= '
                LEFT JOIN   wcf1_category category
                ON          trophy.categoryID = category.categoryID';
            $trophyList->sqlConditionJoins .= '
                LEFT JOIN   wcf1_category category
                ON          trophy.categoryID = category.categoryID';

            $trophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
            $trophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        }

        $trophyList->readObjects();

        $returnValues = [];
        foreach ($userIDs as $userID) {
            $returnValues[$userID] = [];
        }

        foreach ($trophyList as $trophy) {
            $returnValues[$trophy->userID][$trophy->getObjectID()] = $trophy;
        }

        return $returnValues;
    }
}
