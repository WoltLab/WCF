<?php

namespace wcf\system\interaction\admin;

use wcf\data\attachment\AdministrativeAttachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\interaction\admin\AttachmentInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

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
        if (!WCF::getSession()->getPermission('admin.attachment.canManageAttachment')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction(
                'core/attachments/%s',
                static function (AdministrativeAttachment $attachment): bool {
                    // `AttachmentGridView` hides attachments of private object types, therefore
                    // the permission alone does not grant access to this particular attachment.
                    if (ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID)->private) {
                        return false;
                    }

                    return $attachment->canDelete();
                }
            ),
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
