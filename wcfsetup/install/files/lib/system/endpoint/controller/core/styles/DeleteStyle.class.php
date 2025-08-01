<?php

namespace wcf\system\endpoint\controller\core\styles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\style\Style;
use wcf\data\style\StyleAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the style with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/styles/{id:\d+}')]
final class DeleteStyle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $style = Helper::fetchObjectFromRequestParameter($variables['id'], Style::class);

        $this->assertStyleCanBeDeleted($style);

        $action = new StyleAction([$style], 'delete');
        $action->executeAction();

        return new JsonResponse([]);
    }

    private function assertStyleCanBeDeleted(Style $style): void
    {
        WCF::getSession()->checkPermissions(['admin.style.canManageStyle']);

        if ($style->isDefault) {
            throw new IllegalLinkException();
        }
    }
}
