<?php

namespace wcf\data\user\rank;

use wcf\data\I18nDatabaseObjectList;
use wcf\data\user\rank\UserRank;

/**
 * I18n implementation of user rank list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 *
 * @method      UserRank        current()
 * @method      UserRank[]      getObjects()
 * @method      UserRank|null   getSingleObject()
 * @method      UserRank|null   search($objectID)
 * @property    UserRank[]      $objects
 */
class I18nUserRankList extends I18nDatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $i18nFields = ['rankTitle' => 'rankTitleI18n'];

    /**
     * @inheritDoc
     */
    public $className = UserRank::class;
}
