<?php

namespace wcf\system\endpoint\controller\core\users\trophies;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the user trophy with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest('/core/users/trophies/{id:\d+}')]
class DeleteUserTrophy implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $userTrophy = Helper::fetchObjectFromRequestParameter($variables['id'], UserTrophy::class);

        $this->assertUserTrophyCanBeDeleted($userTrophy);

        (new UserTrophyAction([$userTrophy], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertUserTrophyCanBeDeleted(UserTrophy $userTrophy): void
    {
        if (!\MODULE_TROPHY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.trophy.canAwardTrophy']);

        if ($userTrophy->getTrophy()->awardAutomatically) {
            throw new PermissionDeniedException();
        }
    }
}
