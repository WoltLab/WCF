<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionEmbeddedObjects;

/**
 * Represents a collection of article contents.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<ArticleContent>
 */
class ArticleContentCollection extends DatabaseObjectCollection
{
    use TCollectionEmbeddedObjects;
}
