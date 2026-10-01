<?php

namespace wcf\data\cronjob;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionL10n;
use wcf\system\l10n\L10nDefinition;

/**
 * Represents a collection of cronjobs.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Cronjob>
 */
class CronjobCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return Cronjob::getL10nDefinition();
    }
}
