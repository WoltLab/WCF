<?php

namespace wcf\system\interaction\bulk\user;

use wcf\data\DatabaseObject;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\event\interaction\bulk\user\MediaManagerBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteraction;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk interaction provider for the media manager.
 *
 * The `insert` interaction is handled by the media manager itself, which
 * listens for the `bulk-interaction` event of the list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaManagerBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new class('insert') extends AbstractBulkInteraction {
                #[\Override]
                public function render(array $objects): string
                {
                    $label = WCF::getLanguage()->get('wcf.media.button.insert');
                    $objectIDs = StringUtil::encodeHTML(\json_encode(
                        \array_values(\array_map(static fn(DatabaseObject $object) => $object->getObjectID(), $objects)),
                        \JSON_THROW_ON_ERROR
                    ));

                    return <<<HTML
                        <button type="button" data-bulk-interaction="insert" data-object-ids="{$objectIDs}">
                            {$label}
                        </button>
                        HTML;
                }
            },
            new BulkDeleteInteraction(
                'core/media/%s',
                static fn(Media $media) => $media->canManage()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new MediaManagerBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return MediaList::class;
    }
}
