<?php

namespace wcf\system\endpoint\controller\core\smilies\categories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\category\Category;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyList;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of smilies in the category with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[GetRequest('/core/smilies/categories/{id:\d+}/show-order')]
final class GetSmileyShowOrder implements IController
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

        return (new ShowOrderHandler($items))->toJsonResponse();
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
}
