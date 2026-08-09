<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\acp\action\TagSynonymAction;
use wcf\data\tag\TagList;
use wcf\event\interaction\bulk\admin\TagBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkFormBuilderDialogInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for tags.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class TagBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_TAGGING === 0
            || !WCF::getSession()->getPermission('admin.content.tag.canManageTag')
        ) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/tags/%s'),
            new class(
                "set-as-synonyms",
                TagSynonymAction::class,
                "wcf.acp.tag.setAsSynonyms"
            ) extends BulkFormBuilderDialogInteraction {
                #[\Override]
                public function render(array $objects): string
                {
                    if (\count($objects) < 2) {
                        return '';
                    }

                    return parent::render($objects);
                }
            }
        ]);

        EventHandler::getInstance()->fire(
            new TagBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return TagList::class;
    }
}
