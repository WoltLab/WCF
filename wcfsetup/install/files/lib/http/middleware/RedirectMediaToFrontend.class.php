<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\page\MediaPage;
use wcf\system\request\RequestHandler;
use wcf\system\WCFACP;

/**
 * Redirect all media requests in the ACP to the frontend.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RedirectMediaToFrontend implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            return $handler->handle($request);
        }

        if (WCFACP::inRescueMode()) {
            return $handler->handle($request);
        }

        $controller = RequestHandler::getInstance()->getActiveRequest()->getClassName();
        if ($controller !== MediaPage::class) {
            return $handler->handle($request);
        }

        return new RedirectResponse(
            $request->getUri()->withPath(
                \str_replace('/acp', '', $request->getUri()->getPath())
            ),
            301
        );
    }
}
