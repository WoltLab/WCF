<?php

namespace wcf\system\interaction\admin;

use wcf\action\MediaEditAction;
use wcf\data\media\Media;
use wcf\event\interaction\admin\MediaInteractionCollecting;
use wcf\page\MediaPage;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\Divider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\interaction\LinkInteraction;
use wcf\system\request\LinkHandler;

/**
 * Interaction provider for media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new LinkInteraction('open', MediaPage::class, 'wcf.media.button.open'),
            new Divider(),
            new FormBuilderDialogInteraction(
                'edit',
                LinkHandler::getInstance()->getControllerLink(MediaEditAction::class, ['id' => '%s']),
                'wcf.global.button.edit',
                static fn(Media $media) => $media->canManage()
            ),
            new DeleteInteraction(
                'core/media/%s',
                static fn(Media $media) => $media->canManage()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new MediaInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Media::class;
    }
}
