<?php

namespace wcf\system\interaction\admin;

use wcf\data\tag\Tag;
use wcf\event\interaction\admin\TagInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for tags.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class TagInteractions extends AbstractInteractionProvider
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
            new DeleteInteraction("core/tags/%s")
        ]);

        EventHandler::getInstance()->fire(
            new TagInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Tag::class;
    }
}
