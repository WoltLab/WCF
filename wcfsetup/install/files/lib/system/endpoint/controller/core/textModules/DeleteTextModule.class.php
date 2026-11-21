<?php

namespace wcf\system\endpoint\controller\core\textModules;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\textModule\DeleteTextModules;
use wcf\data\text\module\TextModule;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the text module with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/text-modules/{id:\d+}')]
final class DeleteTextModule implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!TextModule::canUse()) {
            throw new PermissionDeniedException();
        }

        $textModule = Helper::fetchObjectFromRequestParameter($variables['id'], TextModule::class);

        new DeleteTextModules([$textModule])();

        return new JsonResponse([]);
    }
}
