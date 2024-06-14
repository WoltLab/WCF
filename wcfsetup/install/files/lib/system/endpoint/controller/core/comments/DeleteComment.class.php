<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * API endpoint for the deletion of comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[DeleteRequest('/core/comments/{id:\d+}')]
final class DeleteComment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $comment = Helper::fetchObjectFromRequestParameter($variables['id'], Comment::class);

        $this->assertCommentIsDeletable($comment);

        (new \wcf\system\comment\command\DeleteComments([$comment]))();

        return new JsonResponse([]);
    }

    private function assertCommentIsDeletable(Comment $comment): void
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
        if (!$objectType->getProcessor()->canDeleteComment($comment)) {
            throw new PermissionDeniedException();
        }
    }
}
