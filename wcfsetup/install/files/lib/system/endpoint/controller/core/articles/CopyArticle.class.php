<?php

namespace wcf\system\endpoint\controller\core\articles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\acp\form\ArticleEditForm;
use wcf\data\article\Article;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;

/**
 * Creates an unpublished copy of the article with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/articles/{id:\d+}/copy')]
final class CopyArticle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (\MODULE_ARTICLE === 0) {
            throw new IllegalLinkException();
        }

        $article = Helper::fetchObjectFromRequestParameter($variables['id'], Article::class);
        if (!$article->canEdit()) {
            throw new PermissionDeniedException();
        }
        if ($article->isDeleted !== 0) {
            throw new IllegalLinkException();
        }

        $newArticle = new \wcf\command\article\CopyArticle($article)();

        return new JsonResponse([
            'redirectUrl' => LinkHandler::getInstance()->getControllerLink(
                ArticleEditForm::class,
                ['id' => $newArticle->articleID]
            ),
        ]);
    }
}
