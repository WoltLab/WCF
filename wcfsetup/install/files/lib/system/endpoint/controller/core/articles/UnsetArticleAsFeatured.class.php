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

/**
 * Removes the featured flag from the article with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/articles/{id:\d+}/unset-as-featured')]
final class UnsetArticleAsFeatured implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (\MODULE_ARTICLE === 0) {
            throw new IllegalLinkException();
        }

        if (!Article::canFeature()) {
            throw new PermissionDeniedException();
        }

        $article = Helper::fetchObjectFromRequestParameter($variables['id'], Article::class);

        if ($article->isFeatured !== 0) {
            new \wcf\command\article\UnsetArticleAsFeatured($article)();
        }

        return new JsonResponse([]);
    }
}
