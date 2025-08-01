<?php

namespace wcf\system\endpoint\controller\core\reactions\types;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of reaction types.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/reactions/types/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertReactionTypeCanBeSorted();

        $reactionTypeList = new ReactionTypeList();
        $reactionTypeList->sqlOrderBy = 'showOrder ASC';
        $reactionTypeList->readObjects();

        $items = \array_map(
            static fn(ReactionType $reactionType) => new ShowOrderItem(
                $reactionType->reactionTypeID,
                $reactionType->getTitle()
            ),
            $reactionTypeList->getObjects()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }

    private function assertReactionTypeCanBeSorted(): void
    {
        if (!\MODULE_LIKE) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.reaction.canManageReactionType"]);
    }
}
