<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\cache\command\ClearCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Clears the cache.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CacheClearAction implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->getPermission('admin.management.canRebuildData')) {
            throw new PermissionDeniedException();
        }

        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            $command = new ClearCache();
            $command();

            return new EmptyResponse();
        } else {
            throw new \LogicException('Unreachable');
        }
    }
}
