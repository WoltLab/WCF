<?php

namespace wcf\system\endpoint\controller\core\labels;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\label\Label;
use wcf\data\label\LabelAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * Deletes the label with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/labels/{id:\d+}")]
final class DeleteLabel implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $label = Helper::fetchObjectFromRequestParameter($variables['id'], Label::class);

        $this->assertLabelCanBeDeleted();

        (new LabelAction([$label], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertLabelCanBeDeleted(): void
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);
    }
}
