<?php

namespace wcf\system\endpoint\controller\core\notices;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\notice\Notice;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Enables the user notices with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/notices/{id:\d+}/enable")]
final class EnableNotice implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $notice = Helper::fetchObjectFromRequestParameter($variables['id'], Notice::class);

        $this->assertNoticeCanBeEnabled();

        (new \wcf\command\notice\EnableNotice($notice))();

        return new JsonResponse([]);
    }

    private function assertNoticeCanBeEnabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.notice.canManageNotice']);
    }
}
