<?php

namespace wcf\system\endpoint\controller\core\labels\groups;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\label\LabelHandler;
use wcf\system\showOrder\ShowOrderHandler;
use wcf\system\showOrder\ShowOrderItem;
use wcf\system\WCF;

/**
 * Retrieves the show order of label groups.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/labels/groups/show-order')]
final class GetShowOrder implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);

        $items = \array_map(
            static fn(ViewableLabelGroup $labelGroup) => new ShowOrderItem($labelGroup->groupID, $labelGroup->getTitle()),
            LabelHandler::getInstance()->getLabelGroups()
        );

        return (new ShowOrderHandler($items))->toJsonResponse();
    }
}
