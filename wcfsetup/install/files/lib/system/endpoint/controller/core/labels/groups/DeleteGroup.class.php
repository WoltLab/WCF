<?php

namespace wcf\system\endpoint\controller\core\labels\groups;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * Deletes the label group with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/labels/groups/{id:\d+}')]
final class DeleteGroup implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $labelGroup = Helper::fetchObjectFromRequestParameter($variables['id'], LabelGroup::class);

        $this->assertGroupCanBeDeleted();

        (new LabelGroupAction([$labelGroup], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertGroupCanBeDeleted(): void
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);
    }
}
