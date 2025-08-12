<?php

namespace wcf\system\endpoint\controller\core\notices;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\notice\Notice;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;

/**
 * API endpoint to dismiss a notice for the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[PostRequest('/core/notices/{id:\d+}/dismiss')]
final class DismissNotice implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $notice = Helper::fetchObjectFromRequestParameter($variables['id'], Notice::class);

        $this->assertNoticeCanBeDismissed($notice);

        (new \wcf\command\notice\DismissNotice($notice))();

        return new JsonResponse([]);
    }

    private function assertNoticeCanBeDismissed(Notice $notice): void
    {
        if (!$notice->isDismissible) {
            throw new PermissionDeniedException();
        }
    }
}
