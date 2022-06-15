<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\exception\NamedUserException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Checks whether the system environment is unacceptable and prevents processing in that case.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   5.6
 */
final class CheckSystemEnvironment implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            if (!(80100 <= \PHP_VERSION_ID && \PHP_VERSION_ID <= 80299)) {
                \header('HTTP/1.1 500 Internal Server Error');

                throw new NamedUserException(WCF::getLanguage()->get('wcf.global.incompatiblePhpVersion'));
            }
        }

        return $handler->handle($request);
    }
}
