<?php

namespace wcf\system\endpoint\controller\core\labels\groups;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\Label;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\label\LabelHandler;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of the labels in the group with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/labels/groups/{id:\d+}/labels/show-order')]
final class GetLabelShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);

        $labelGroup = Helper::fetchObjectFromRequestParameter($variables['id'], LabelGroup::class);
        $items = \array_map(
            static fn(Label $label) => new ShowOrderItem($label->labelID, $label->getTitle()),
            LabelHandler::getInstance()->getLabelGroup($labelGroup->groupID)->getLabels()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }
}
