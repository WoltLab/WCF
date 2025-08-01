<?php

namespace wcf\system\endpoint\controller\core\articles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\article\Article;
use wcf\data\article\ViewableArticle;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the popover of the article with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/articles/{id:\d+}/popover')]
final class GetArticlePopover implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $article = Helper::fetchObjectFromRequestParameter($variables['id'], Article::class);

        $this->assertArticleIsAccessible($article);

        return new JsonResponse([
            'template' => $this->renderPopover($article),
        ]);
    }

    private function assertArticleIsAccessible(Article $article): void
    {
        if (!$article->canRead()) {
            throw new PermissionDeniedException();
        }
    }

    private function renderPopover(Article $article): string
    {
        return WCF::getTPL()->render('wcf', 'articlePopover', [
            'article' => ViewableArticle::getArticle($article->articleID),
        ]);
    }
}
