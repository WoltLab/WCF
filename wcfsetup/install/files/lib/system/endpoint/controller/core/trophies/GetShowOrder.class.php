<?php

namespace wcf\system\endpoint\controller\core\trophies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/trophies/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertTrophyCanBeSorted();

        $trophyList = new TrophyList();
        $trophyList->sqlOrderBy = 'showOrder ASC';
        $trophyList->readObjects();

        $items = \array_map(
            static fn(Trophy $trophy) => new ShowOrderItem($trophy->trophyID, $trophy->getTitle()),
            $trophyList->getObjects()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }

    private function assertTrophyCanBeSorted(): void
    {
        if (!\MODULE_TROPHY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.trophy.canManageTrophy']);
    }
}
