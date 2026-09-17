<?php

namespace wcf\event\file;

use wcf\data\file\File;
use wcf\event\IPsr14Event;

/**
 * Indicates that a file has been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class FileDeleted implements IPsr14Event
{
    public function __construct(
        public readonly File $file,
    ) {}
}
