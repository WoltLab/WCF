<?php

namespace wcf\data\unfurl\url\image;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionFiles;

/**
 * Represents a collection of unfurled url images.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<UnfurlUrlImage>
 */
class UnfurlUrlImageCollection extends DatabaseObjectCollection
{
    use TCollectionFiles;
}
