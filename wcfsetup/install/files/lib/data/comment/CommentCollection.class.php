<?php

namespace wcf\data\comment;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionEmbeddedObjects;
use wcf\data\TCollectionUserProfiles;

/**
 * Represents a collection of comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Comment>
 */
class CommentCollection extends DatabaseObjectCollection
{
    use TCollectionUserProfiles;
    use TCollectionEmbeddedObjects;
}
