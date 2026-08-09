<?php

namespace wcf\system\interaction\admin;

use wcf\data\label\Label;
use wcf\event\interaction\admin\LabelInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for labels.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class LabelInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.content.label.canManageLabel')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction("core/labels/%s")
        ]);

        EventHandler::getInstance()->fire(
            new LabelInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Label::class;
    }
}
