<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\attachment\AdministrativeAttachment;
use wcf\data\attachment\AdministrativeAttachmentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\interaction\bulk\admin\AttachmentBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for attachments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AttachmentBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.attachment.canManageAttachment')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction(
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
            new AttachmentBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return AdministrativeAttachmentList::class;
    }
}
