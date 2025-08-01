<?php

namespace wcf\system\endpoint\controller\core\ads;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\ad\Ad;
use wcf\data\ad\AdAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the ad with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/ads/{id:\d+}")]
final class DeleteAd implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $ad = Helper::fetchObjectFromRequestParameter($variables['id'], Ad::class);

        $this->assertAdCanBeDeleted();

        (new AdAction([$ad], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertAdCanBeDeleted(): void
    {
        if (!\MODULE_WCF_AD) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.ad.canManageAd']);
    }
}
