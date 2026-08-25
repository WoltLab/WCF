<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionUserProfiles;

/**
 * Represents a collection of user profile visitors.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UserProfileVisitor>
 */
class UserProfileVisitorCollection extends DatabaseObjectCollection
{
    use TCollectionUserProfiles;
}
