<?php

namespace wcf\system\endpoint\controller\core\smilies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of smilies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/smilies/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertSmileyCanBeSorted();

        $smileyList = new SmileyList();
        $smileyList->sqlOrderBy = 'showOrder ASC';
        $smileyList->getConditionBuilder()->add('categoryID IS NULL');
        $smileyList->readObjects();

        $items = \array_map(
            static fn(Smiley $smiley) => new ShowOrderItem(
                $smiley->smileyID,
                $smiley->getTitle()
            ),
            $smileyList->getObjects()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }

    private function assertSmileyCanBeSorted(): void
    {
        if (!\MODULE_SMILEY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.smiley.canManageSmiley"]);
    }
}
