<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\acp\action\MediaSetCategoryAction;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\event\interaction\bulk\admin\MediaBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkFormBuilderDialogInteraction;

/**
 * Bulk interaction provider for media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new BulkFormBuilderDialogInteraction(
                'set-category',
                MediaSetCategoryAction::class,
                'wcf.media.setCategory',
                static fn(Media $media) => $media->canManage()
            ),
            new BulkDeleteInteraction(
                'core/media/%s',
                static fn(Media $media) => $media->canManage()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new MediaBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return MediaList::class;
    }
}
