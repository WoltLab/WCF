<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of profile visitors.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<UserProfileVisitor>
 */
class UserProfileVisitorList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'user_profile_visitor.time DESC';
}
