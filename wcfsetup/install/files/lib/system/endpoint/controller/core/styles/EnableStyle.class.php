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
 * Enables the style with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/styles/{id:\d+}/enable')]
final class EnableStyle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $style = Helper::fetchObjectFromRequestParameter($variables['id'], Style::class);

        $this->assertStyleCanBeEnabled();

        if ($style->isDisabled) {
            (new \wcf\command\style\EnableStyle($style))();
        }

        return new JsonResponse([]);
    }

    private function assertStyleCanBeEnabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.style.canManageStyle']);
    }
}
