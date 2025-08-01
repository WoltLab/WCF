<?php

namespace wcf\system\endpoint\controller\core\boxes;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Enables the box with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/boxes/{id:\d+}/enable')]
final class EnableBox implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $box = Helper::fetchObjectFromRequestParameter($variables['id'], Box::class);

        $this->assertBoxCanBeEnabled($box);

        (new BoxAction([$box], 'toggle'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertBoxCanBeEnabled(Box $box): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageBox']);

        if (!$box->isDisabled) {
            throw new PermissionDeniedException();
        }
    }
}
