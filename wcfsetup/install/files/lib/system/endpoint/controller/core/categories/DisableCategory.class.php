<?php

namespace wcf\system\endpoint\controller\core\categories;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\category\Category;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;

/**
 * Disables the category with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest("/core/categories/{id:\d+}/disable")]
final class DisableCategory implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $category = Helper::fetchObjectFromRequestParameter($variables['id'], Category::class);

        $this->assertCategoryCanBeDisabled($category);

        if (!$category->isDisabled) {
            (new \wcf\command\category\DisableCategory($category))();
        }

        return new JsonResponse([]);
    }

    private function assertCategoryCanBeDisabled(Category $category): void
    {
        if (!$category->getObjectType()->getProcessor()->canEditCategory()) {
            throw new PermissionDeniedException();
        }
    }
}
