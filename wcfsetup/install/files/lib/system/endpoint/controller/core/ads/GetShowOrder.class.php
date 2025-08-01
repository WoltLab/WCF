<?php

namespace wcf\system\endpoint\controller\core\ads;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\ad\Ad;
use wcf\data\ad\AdList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of ads.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/ads/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertAdCanBeSorted();

        $adList = new AdList();
        $adList->sqlOrderBy = 'showOrder ASC';
        $adList->readObjects();

        $items = \array_map(
            static fn(Ad $ad) => new ShowOrderItem($ad->adID, $ad->getTitle()),
            $adList->getObjects()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }

    private function assertAdCanBeSorted(): void
    {
        if (!\MODULE_WCF_AD) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.ad.canManageAd']);
    }
}
