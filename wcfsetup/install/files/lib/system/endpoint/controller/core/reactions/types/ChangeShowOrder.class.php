<?php

namespace wcf\system\endpoint\controller\core\reactions\types;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Saves the show order of reaction types.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/reactions/types/show-order')]
final class ChangeShowOrder implements IController
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

        $sortedItems = (new ShowOrderHandler($items))->getSortedItemsFromRequest($request);
        $this->saveShowOrder($sortedItems);

        return new JsonResponse([]);
    }

    private function assertReactionTypeCanBeSorted(): void
    {
        if (!\MODULE_LIKE) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.reaction.canManageReactionType"]);
    }

    /**
     * @param list<ShowOrderItem> $items
     */
    private function saveShowOrder(array $items): void
    {
        WCF::getDB()->beginTransaction();
        $sql = "UPDATE  wcf1_reaction_type
                SET     showOrder = ?
                WHERE   reactionTypeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        ReactionTypeCacheBuilder::getInstance()->reset();
    }
}
