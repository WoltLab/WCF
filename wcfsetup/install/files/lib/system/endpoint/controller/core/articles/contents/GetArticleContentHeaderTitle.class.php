<?php

namespace wcf\system\endpoint\controller\core\articles\contents;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\article\Article;
use wcf\data\article\content\ViewableArticleContent;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the content header title of the article content with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/articles/contents/{id:\d+}/content-header-title')]
final class GetArticleContentHeaderTitle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $articleContent = ViewableArticleContent::getArticleContent((int)$variables['id']);
        if ($articleContent === null) {
            throw new IllegalLinkException();
        }

        $this->assertArticleIsAccessible($articleContent->getArticle()->getDecoratedObject());

        $articleContent->getArticle()->getDiscussionProvider()->setArticleContent($articleContent->getDecoratedObject());

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'articleContentHeaderTitle', [
                'articleContent' => $articleContent,
                'article' => $articleContent->getArticle(),
            ]),
        ]);
    }

    private function assertArticleIsAccessible(Article $article): void
    {
        if (!$article->canRead()) {
            throw new PermissionDeniedException();
        }
    }
}
