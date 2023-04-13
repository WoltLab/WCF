<?php

namespace wcf\http\error;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\box\BoxHandler;
use wcf\system\request\RequestHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Returns a "XSRF validation failed" response.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class XsrfValidationFailedHandler implements RequestHandlerInterface
{
    private const STATUS_CODE = 400;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $errorDetail = ErrorDetail::fromRequest($request);
        $message = WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.sessionExpired');

        if (!RequestHandler::getInstance()->isACPRequest()) {
            BoxHandler::disablePageLayout();
        }
        SessionHandler::getInstance()->disableTracking();

        $preferredType = Helper::getPreferredContentType($request, [
            'text/html',
            'application/json',
        ]);

        return match ($preferredType) {
            'application/json' => new JsonResponse(
                [
                    'message' => $message,
                    'exception' => \ENABLE_DEBUG_MODE ? $errorDetail?->getThrowable()?->__toString() : null,
                ],
                self::STATUS_CODE,
                [],
                \JSON_PRETTY_PRINT
            ),
            'text/html' => new HtmlResponse(
                (new HtmlErrorRenderer())->render(
                    WCF::getLanguage()->getDynamicVariable('wcf.global.error.title'),
                    $message,
                    $errorDetail?->getThrowable()
                ),
                self::STATUS_CODE
            ),
        };
    }
}
