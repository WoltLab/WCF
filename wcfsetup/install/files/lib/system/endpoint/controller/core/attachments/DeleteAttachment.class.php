<?php

namespace wcf\system\endpoint\controller\core\attachments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the attachment with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/attachments/{id:\d+}')]
final class DeleteAttachment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $attachment = Helper::fetchObjectFromRequestParameter($variables['id'], Attachment::class);

        $this->assertAttachmentBeDeleted($attachment);

        (new AttachmentAction([$attachment], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertAttachmentBeDeleted(Attachment $attachment): void
    {
        if (!WCF::getSession()->getPermission("admin.attachment.canManageAttachment") || !$attachment->canDelete()) {
            throw new PermissionDeniedException();
        }

        if (ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID)->private) {
            throw new PermissionDeniedException();
        }
    }
}
