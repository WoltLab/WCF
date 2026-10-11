<?php

namespace wcf\data\user\option\category;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionL10n;
use wcf\system\l10n\L10nDefinition;

/**
 * Collection of user option categories that batch-loads their localized
 * values from the `wcf1_user_option_category_l10n` table.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UserOptionCategory>
 */
class UserOptionCategoryCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return UserOptionCategory::getL10nDefinition();
    }
}
