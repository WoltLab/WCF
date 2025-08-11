<?php

namespace wcf\system\endpoint\controller\core\trophies;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\trophy\Trophy;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Enables the trophy with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest("/core/trophies/{id:\d+}/enable")]
final class EnableTrophy implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $trophy = Helper::fetchObjectFromRequestParameter($variables['id'], Trophy::class);

        $this->assertTrophyCanBeEnabled();

        (new \wcf\command\trophy\EnableTrophy($trophy))();

        return new JsonResponse([]);
    }

    private function assertTrophyCanBeEnabled( ): void
    {
        if (!\MODULE_TROPHY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.trophy.canManageTrophy']);
    }
}
