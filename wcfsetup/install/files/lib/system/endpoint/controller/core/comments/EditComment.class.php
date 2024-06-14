<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\http\Helper;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\WCF;

/**
 * API endpoint for starting the editing of a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/{id:\d+}/edit')]
final class EditComment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $comment = Helper::fetchObjectFromRequestParameter($variables['id'], Comment::class);

        $this->assertCommentIsEditable($comment);

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission('user.comment.disallowedBBCodes')
        ));

        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($comment->message, 'com.woltlab.wcf.comment');

        return new JsonResponse([
            'template' => WCF::getTPL()->fetch('commentEditor', 'wcf', [
                'comment' => $comment,
                'text' => $upcastProcessor->getHtml(),
                'wysiwygSelector' => 'commentEditor' . $comment->commentID,
            ]),
        ]);
    }

    private function assertCommentIsEditable(Comment $comment): void
    {
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->canEditComment($comment)) {
            throw new PermissionDeniedException();
        }
    }
}
