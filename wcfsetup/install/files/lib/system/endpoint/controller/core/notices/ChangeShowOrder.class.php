<?php

namespace wcf\system\endpoint\controller\core\notices;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\notice\Notice;
use wcf\data\notice\NoticeList;
use wcf\system\cache\eager\NoticeCache;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * API endpoint for changing the show order of notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/notices/show-order')]
final class ChangeShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.notice.canManageNotice']);

        $noticeList = new NoticeList();
        $noticeList->sqlOrderBy = 'showOrder ASC';
        $noticeList->readObjects();

        $items = \array_map(
            static fn(Notice $notice) => new ShowOrderItem($notice->noticeID, $notice->getTitle()),
            $noticeList->getObjects()
        );

        $sortedItems = (new ShowOrderHandler($items))->getSortedItemsFromRequest($request);
        $this->saveShowOrder($sortedItems);

        return new JsonResponse([]);
    }

    /**
     * @param list<ShowOrderItem> $items
     */
    private function saveShowOrder(array $items): void
    {
        WCF::getDB()->beginTransaction();
        $sql = "UPDATE  wcf1_notice
                SET     showOrder = ?
                WHERE   noticeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        (new NoticeCache())->rebuild();
    }
}
