<?php

namespace wcf\command\media;

use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\event\media\MediaCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new media file.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateMedia
{
    public function __construct(
        private readonly MediaBuilder $builder,
    ) {}

    public function __invoke(): Media
    {
        $media = $this->builder->create();

        EventHandler::getInstance()->fire(new MediaCreated($media, $this->builder));

        return $media;
    }
}
