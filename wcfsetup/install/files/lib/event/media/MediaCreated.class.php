<?php

namespace wcf\event\media;

use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a media file has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaCreated implements IPsr14Event
{
    public function __construct(
        public readonly Media $media,
        public readonly MediaBuilder $builder,
    ) {}
}
