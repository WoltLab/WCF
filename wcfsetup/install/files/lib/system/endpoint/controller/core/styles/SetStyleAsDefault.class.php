<?php

namespace wcf\system\endpoint\controller\core\styles;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Flags the style with the given ID as default style.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/styles/{id:\d+}/set-as-default')]
final class SetStyleAsDefault implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $style = Helper::fetchObjectFromRequestParameter($variables['id'], Style::class);

        $this->assertStyleCanBeSetAsDefault();

        if (!$style->isDefault) {
            $editor = new StyleEditor($style);
            $editor->setAsDefault();
        }

        return new JsonResponse([]);
    }

    private function assertStyleCanBeSetAsDefault(): void
    {
        WCF::getSession()->checkPermissions(['admin.style.canManageStyle']);
    }
}
