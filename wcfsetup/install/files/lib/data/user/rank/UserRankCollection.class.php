<?php

namespace wcf\data\user\rank;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionL10n;
use wcf\system\l10n\L10nDefinition;

/**
 * Represents a collection of user ranks.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UserRank>
 */
class UserRankCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return UserRank::getL10nDefinition();
    }
}
