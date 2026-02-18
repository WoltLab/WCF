<?php

namespace wcf\system\endpoint\controller\core\labels\groups;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\Label;
use wcf\data\label\LabelEditor;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\label\LabelHandler;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Saves the show order of the labels in the group with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/labels/groups/{id:\d+}/labels/show-order')]
final class ChangeLabelShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);

        $labelGroup = Helper::fetchObjectFromRequestParameter($variables['id'], LabelGroup::class);
        if ($labelGroup->sortAlphabetically) {
            throw new IllegalLinkException();
        }

        $items = \array_map(
            static fn(Label $label) => new ShowOrderItem($label->labelID, $label->getTitle()),
            LabelHandler::getInstance()->getLabelGroup($labelGroup->groupID)->getLabels()
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
        $sql = "UPDATE  wcf1_label
                SET     showOrder = ?
                WHERE   labelID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        for ($i = 0, $length = \count($items); $i < $length; $i++) {
            $statement->execute([
                $i + 1,
                $items[$i]->id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        LabelEditor::resetCache();
    }
}
