<?php

namespace wcf\system\endpoint\controller\core\styles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\style\Style;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Copies the style with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/styles/{id:\d+}/copy')]
final class CopyStyle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $style = Helper::fetchObjectFromRequestParameter($variables['id'], Style::class);

        $this->assertStyleCanBeCopied();

        (new \wcf\command\style\CopyStyle($style))();

        return new JsonResponse([]);
    }

    private function assertStyleCanBeCopied(): void
    {
        WCF::getSession()->checkPermissions(['admin.style.canManageStyle']);
    }
}
