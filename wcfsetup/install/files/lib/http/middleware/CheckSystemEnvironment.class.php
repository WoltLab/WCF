<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\error\HtmlErrorRenderer;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Checks whether the system environment is unacceptable and prevents processing in that case.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class CheckSystemEnvironment implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            if (!(80100 <= \PHP_VERSION_ID && \PHP_VERSION_ID <= 80499)) {
                return new HtmlResponse(
                    (new HtmlErrorRenderer())->render(
                        WCF::getLanguage()->getDynamicVariable('wcf.global.error.title'),
                        WCF::getLanguage()->get('wcf.global.incompatiblePhpVersion'),
                    ),
                    500
                );
            }
        }

        return $handler->handle($request);
    }
}
