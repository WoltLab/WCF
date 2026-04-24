<?php

namespace wcf\system\endpoint\controller\core\articles\contents;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\article\Article;
use wcf\data\article\content\ArticleContent;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
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
        $articleContent = Helper::fetchObjectFromRequestParameter($variables['id'], ArticleContent::class);

        $this->assertArticleIsAccessible($articleContent->getArticle());

        $articleContent->getArticle()->getDiscussionProvider()->setArticleContent($articleContent);

        if ($articleContent->languageID !== null) {
            $articleContent->getArticle()->setActiveLanguageID($articleContent->languageID);
        }

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
