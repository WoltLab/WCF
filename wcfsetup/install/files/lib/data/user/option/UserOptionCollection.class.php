<?php

namespace wcf\data\user\option;

use wcf\data\option\OptionCollection;
use wcf\data\TCollectionL10n;
use wcf\system\l10n\L10nDefinition;

/**
 * Collection of user options that batch-loads their localized values from the
 * `wcf1_user_option_l10n` table.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class UserOptionCollection extends OptionCollection
{
    use TCollectionL10n;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return UserOption::getL10nDefinition();
    }
}
