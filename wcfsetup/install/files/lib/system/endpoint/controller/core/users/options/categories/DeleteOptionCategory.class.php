<?php

namespace wcf\system\endpoint\controller\core\users\options\categories;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the user option category with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/users/options/categories/{id:\d+}')]
final class DeleteOptionCategory implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $category = Helper::fetchObjectFromRequestParameter($variables['id'], UserOptionCategory::class);

        $this->assertCategoryCanBeDeleted($category);

        new \wcf\command\user\option\category\DeleteOptionCategory($category)();

        return new JsonResponse([]);
    }

    private function assertCategoryCanBeDeleted(UserOptionCategory $category): void
    {
        WCF::getSession()->checkPermissions(['admin.user.canManageUserOption']);

        if (!$category->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
