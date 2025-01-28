<?php

namespace wcf\system\endpoint\controller\core\users\options;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\option\UserOption;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * API endpoint for disabling user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/users/options/{id:\d+}/disable')]
final class DisableOption implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $option = Helper::fetchObjectFromRequestParameter($variables['id'], UserOption::class);

        $this->assertOptionCanBeDisabled();

        if (!$option->isDisabled) {
            (new \wcf\system\user\option\command\DisableOption($option))();
        }

        return new JsonResponse([]);
    }

    private function assertOptionCanBeDisabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.user.canManageUserOption']);
    }
}
