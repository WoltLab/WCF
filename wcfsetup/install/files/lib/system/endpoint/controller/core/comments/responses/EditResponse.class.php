<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\http\Helper;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\WCF;

/**
 * API endpoint for starting the editing of a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/responses/{id:\d+}/edit')]
final class EditResponse implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $response = Helper::fetchObjectFromRequestParameter($variables['id'], CommentResponse::class);

        $this->assertResponseIsEditable($response);

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission('user.comment.disallowedBBCodes')
        ));

        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($response->message, 'com.woltlab.wcf.comment.response');

        return new JsonResponse([
            'template' => WCF::getTPL()->fetch('commentResponseEditor', 'wcf', [
                'response' => $response,
                'text' => $upcastProcessor->getHtml(),
                'wysiwygSelector' => 'commentResponseEditor' . $response->responseID,
            ]),
        ]);
    }

    private function assertResponseIsEditable(CommentResponse $response): void
    {
        $manager = CommentHandler::getInstance()->getCommentManagerByID($response->getComment()->objectTypeID);
        if (!$manager->canEditResponse($response)) {
            throw new PermissionDeniedException();
        }
    }
}
