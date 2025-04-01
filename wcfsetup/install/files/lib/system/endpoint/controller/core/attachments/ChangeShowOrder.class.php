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

        $this->assertAttachmentsCanBeSorted($attachmentHandler, $parameters->attachmentIDs);

        $this->saveShowOrder($parameters->attachmentIDs);

        return new JsonResponse([]);
    }

    /**
     * @param list<int> $attachmentIDs
     */
    private function assertAttachmentsCanBeSorted(AttachmentHandler $attachmentHandler, array $attachmentIDs): void
    {
        if (!$attachmentHandler->canUpload()) {
            throw new PermissionDeniedException();
        }

        $attachmentList = $attachmentHandler->getAttachmentList();
        foreach ($attachmentIDs as $attachmentID) {
            if (!\in_array($attachmentID, $attachmentList->getObjectIDs(), true)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @param list<int> $attachmentIDs
     */
    private function saveShowOrder(array $attachmentIDs): void
    {
        WCF::getDB()->beginTransaction();

        $sql = "UPDATE wcf1_attachment
                SET    showOrder = ?
                WHERE  attachmentID = ?";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($attachmentIDs as $showOrder => $attachmentID) {
            $statement->execute([
                $showOrder + 1,
                $attachmentID,
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
        /** @var list<positive-int> */
        public readonly array $attachmentIDs,
    ) {
    }
}
