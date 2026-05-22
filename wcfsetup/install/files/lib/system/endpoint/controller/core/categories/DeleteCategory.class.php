<?php

namespace wcf\system\endpoint\controller\core\categories;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the category with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest("/core/categories/{id:\d+}")]
final class DeleteCategory implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $category = Helper::fetchObjectFromRequestParameter($variables['id'], Category::class);

        $this->assertCategoryCanBeDeleted($category);

        (new CategoryAction([$category->categoryID], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertCategoryCanBeDeleted(Category $category): void
    {
        if (!$category->getObjectType()->getProcessor()->canDeleteCategory()) {
            throw new PermissionDeniedException();
        }
    }
}
