<?php

namespace wcf\data\label\group;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionL10n;
use wcf\system\l10n\L10nDefinition;

/**
 * Represents a collection of label groups.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<LabelGroup>
 */
class LabelGroupCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return LabelGroup::getL10nDefinition();
    }
}
