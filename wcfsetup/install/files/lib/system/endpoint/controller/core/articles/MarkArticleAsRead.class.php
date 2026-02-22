<?php

namespace wcf\system\endpoint\controller\core\articles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\article\Article;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Marks the article with the given ID as read for the current user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/articles/{id:\d+}/mark-as-read')]
final class MarkArticleAsRead implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!MODULE_ARTICLE) {
            throw new IllegalLinkException();
        }

        $article = Helper::fetchObjectFromRequestParameter($variables['id'], Article::class);
        $this->assertArticleIsAccessible($article);

        (new \wcf\command\article\MarkArticleAsRead($article))();

        return new JsonResponse([]);
    }

    private function assertArticleIsAccessible(Article $article): void
    {
        if (!WCF::getUser()->userID || !$article->canRead()) {
            throw new PermissionDeniedException();
        }
    }
}
