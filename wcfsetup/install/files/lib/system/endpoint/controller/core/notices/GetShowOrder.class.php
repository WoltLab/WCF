<?php

namespace wcf\system\endpoint\controller\core\notices;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\notice\Notice;
use wcf\data\notice\NoticeList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of user notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/notices/show-order')]
final class GetShowOrder implements IController
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

        return (new ShowOrderHandler($items))->toJsonResponse();
    }
}
