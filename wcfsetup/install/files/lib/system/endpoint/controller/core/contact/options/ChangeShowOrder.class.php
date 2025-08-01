<?php

namespace wcf\system\endpoint\controller\core\contact\options;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionList;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Saves the show order of contact options.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest('/core/contact/options/show-order')]
final class ChangeShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertOptionCanBeSorted();

        $optionList = new ContactOptionList();
        $optionList->sqlOrderBy = 'showOrder ASC';
        $optionList->readObjects();

        $items = \array_map(
            static fn(ContactOption $option) => new ShowOrderItem(
                $option->optionID,
                $option->getTitle()
            ),
            $optionList->getObjects()
        );

        $sortedItems = (new ShowOrderHandler($items))->getSortedItemsFromRequest($request);
        $this->saveShowOrder($sortedItems);

        return new JsonResponse([]);
    }


    private function assertOptionCanBeSorted(): void
    {
        if (!\MODULE_CONTACT_FORM) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.contact.canManageContactForm"]);
    }

    /**
     * @param list<ShowOrderItem> $items
     */
    private function saveShowOrder(array $items): void
    {
        WCF::getDB()->beginTransaction();
        $sql = "UPDATE  wcf1_contact_option
                SET     showOrder = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
