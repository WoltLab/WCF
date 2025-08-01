<?php

namespace wcf\system\endpoint\controller\core\trophies;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the trophy with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest("/core/trophies/{id:\d+}")]
final class DeleteTrophy implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $trophy = Helper::fetchObjectFromRequestParameter($variables['id'], Trophy::class);

        $this->assertTrophyCanBeDeleted();

        (new TrophyAction([$trophy], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertTrophyCanBeDeleted(): void
    {
        if (!\MODULE_TROPHY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.trophy.canManageTrophy']);
    }
}
