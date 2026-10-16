<?php

namespace wcf\command\media;

use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\event\media\MediaUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates an existing media file.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateMedia
{
    public function __construct(
        private readonly MediaBuilder $builder,
    ) {}

    public function __invoke(): Media
    {
        $media = $this->builder->update();

        EventHandler::getInstance()->fire(new MediaUpdated($media, $this->builder));

        return $media;
    }
}
