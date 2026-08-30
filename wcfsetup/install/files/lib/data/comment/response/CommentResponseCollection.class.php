<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionEmbeddedObjects;
use wcf\data\TCollectionUserProfiles;

/**
 * Represents a collection of comment responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<CommentResponse>
 */
class CommentResponseCollection extends DatabaseObjectCollection
{
    use TCollectionUserProfiles;
    use TCollectionEmbeddedObjects;
}
