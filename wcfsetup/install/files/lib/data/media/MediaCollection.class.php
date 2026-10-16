<?php

namespace wcf\data\media;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionL10n;
use wcf\data\TCollectionUserProfiles;
use wcf\system\l10n\L10nDefinition;

/**
 * Represents a collection of media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Media>
 */
class MediaCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;
    use TCollectionUserProfiles;

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return Media::getL10nDefinition();
    }
}
