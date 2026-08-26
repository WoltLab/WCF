<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionFiles;

/**
 * Represents a collection of attachments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Attachment>
 */
class AttachmentCollection extends DatabaseObjectCollection
{
    use TCollectionFiles;
}
