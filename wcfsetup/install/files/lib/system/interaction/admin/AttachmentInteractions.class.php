<?php

namespace wcf\system\interaction\admin;

use wcf\data\attachment\AdministrativeAttachment;
use wcf\event\interaction\admin\AttachmentInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for attachments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AttachmentInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('core/attachments/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new AttachmentInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return AdministrativeAttachment::class;
    }
}
