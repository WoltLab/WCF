<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentList;
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
        if (!WCF::getSession()->hasPermission('admin.attachment.canManageAttachment')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction(
                'core/attachments/%s',
                static function (Attachment $attachment): bool {
                    // `AttachmentGridView` hides attachments of private object types, therefore
                    // the permission alone does not grant access to this particular attachment.
                    if (ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID)->private === '1') {
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
        return AttachmentList::class;
    }
}
