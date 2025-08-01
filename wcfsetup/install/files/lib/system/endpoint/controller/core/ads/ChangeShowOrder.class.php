<?php

namespace wcf\system\endpoint\controller\core\ads;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\ad\Ad;
use wcf\data\ad\AdList;
use wcf\system\cache\builder\AdCacheBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Saves the show order of ads.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/ads/show-order')]
final class ChangeShowOrder implements IController
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

        $sortedItems = (new ShowOrderHandler($items))->getSortedItemsFromRequest($request);
        $this->saveShowOrder($sortedItems);

        return new JsonResponse([]);
    }

    private function assertAdCanBeSorted(): void
    {
        if (!\MODULE_WCF_AD) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.ad.canManageAd']);
    }

    /**
     * @param list<ShowOrderItem> $items
     */
    private function saveShowOrder(array $items): void
    {
        WCF::getDB()->beginTransaction();
        $sql = "UPDATE  wcf1_ad
                SET     showOrder = ?
                WHERE   adID = ?";
        $statement = WCF::getDB()->prepare($sql);
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        AdCacheBuilder::getInstance()->reset();
    }
}
