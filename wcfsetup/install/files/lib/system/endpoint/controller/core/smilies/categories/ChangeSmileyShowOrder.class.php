<?php

namespace wcf\system\endpoint\controller\core\smilies\categories;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\category\Category;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyList;
use wcf\http\Helper;
use wcf\system\cache\builder\SmileyCacheBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Saves the show order of smilies in the category with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/smilies/categories/{id:\d+}/show-order')]
final class ChangeSmileyShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $smileyCategory = Helper::fetchObjectFromRequestParameter($variables['id'], Category::class);

        $this->assertSmileyCanBeSorted($smileyCategory);

        $smileyList = new SmileyList();
        $smileyList->sqlOrderBy = 'showOrder ASC';
        $smileyList->getConditionBuilder()->add('categoryID = ?', [$smileyCategory->categoryID]);
        $smileyList->readObjects();

        $items = \array_map(
            static fn(Smiley $smiley) => new ShowOrderItem(
                $smiley->smileyID,
                $smiley->getTitle()
            ),
            $smileyList->getObjects()
        );

        $sortedItems = (new ShowOrderHandler($items))->getSortedItemsFromRequest($request);
        $this->saveShowOrder($sortedItems);

        return new JsonResponse([]);
    }

    private function assertSmileyCanBeSorted(Category $smileyCategory): void
    {
        if (!\MODULE_SMILEY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.smiley.canManageSmiley"]);

        if (!$smileyCategory->categoryID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @param list<ShowOrderItem> $items
     */
    private function saveShowOrder(array $items): void
    {
        WCF::getDB()->beginTransaction();
        $sql = "UPDATE  wcf1_smiley
                SET     showOrder = ?
                WHERE   smileyID = ?";
        $statement = WCF::getDB()->prepare($sql);
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        SmileyCacheBuilder::getInstance()->reset();
    }
}
