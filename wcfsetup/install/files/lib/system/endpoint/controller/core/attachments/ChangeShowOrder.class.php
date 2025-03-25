<?php

namespace wcf\system\endpoint\controller\core\attachments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * API endpoint for changing the show order of attachments.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/attachments/show-order')]
final class ChangeShowOrder implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, ChangeShowOrderParameters::class);

        $attachmentHandler = new AttachmentHandler(
            $parameters->objectType,
            $parameters->objectID,
            $parameters->tmpHash,
            $parameters->parentObjectID,
        );

        $this->assertAttachmentsCanBeSorted($attachmentHandler, $parameters->fileIDs);

        $this->saveShowOrder($parameters->fileIDs);

        return new JsonResponse([]);
    }

    /**
     * @param int[] $fileIDs
     */
    private function assertAttachmentsCanBeSorted(AttachmentHandler $attachmentHandler, array $fileIDs): void
    {
        if (!$attachmentHandler->canUpload()) {
            throw new PermissionDeniedException();
        }

        foreach ($attachmentHandler->getAttachmentList()->getObjects() as $attachment) {
            if (!\in_array($attachment->fileID, $fileIDs)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @param int[] $fileIDs
     */
    private function saveShowOrder(array $fileIDs): void
    {
        WCF::getDB()->beginTransaction();

        $sql = "UPDATE wcf1_attachment
                SET    showOrder = ?
                WHERE  fileID = ?";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($fileIDs as $showOrder => $fileID) {
            $statement->execute([
                $showOrder + 1,
                $fileID,
            ]);
        }

        WCF::getDB()->commitTransaction();
    }
}

/** @internal */
final class ChangeShowOrderParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $objectType,
        /** @var non-negative-int */
        public readonly int $objectID,
        /** @var non-negative-int */
        public readonly int $parentObjectID,
        public readonly string $tmpHash,
        /** @var int[] */
        public readonly array $fileIDs,
    ) {
    }
}
