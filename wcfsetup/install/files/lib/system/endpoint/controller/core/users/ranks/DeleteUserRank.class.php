<?php

namespace wcf\system\endpoint\controller\core\users\ranks;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * API endpoint for the deletion of user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[DeleteRequest('/core/users/ranks/{id:\d+}')]
final class DeleteUserRank implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.user.rank.canManageRank']);

        $rank = Helper::fetchObjectFromRequestParameter($variables['id'], UserRank::class);

        $action = new UserRankAction([$rank], 'delete');
        $action->executeAction();

        return new JsonResponse([]);
    }
}
