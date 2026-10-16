<?php

namespace wcf\event\media;

use wcf\data\media\Media;
use wcf\event\IPsr14Event;

/**
 * Indicates that media files have been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaDeleted implements IPsr14Event
{
    /**
     * @param Media[] $media
     */
    public function __construct(
        public readonly array $media,
    ) {}
}
