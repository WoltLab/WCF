<?php

namespace wcf\http\error;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\box\BoxHandler;
use wcf\system\notice\NoticeHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Returns an "Offline" response.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class OfflineHandler implements RequestHandlerInterface
{
    private const STATUS_CODE = 503;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        BoxHandler::disablePageLayout();
        NoticeHandler::disableNotices();

        $preferredType = Helper::getPreferredContentType($request, [
            'text/html',
            'application/json',
        ]);

        return HeaderUtil::withNoCacheHeaders(match ($preferredType) {
            'application/json' => new JsonResponse(
                [
                    'message' => WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'),
                ],
                self::STATUS_CODE,
                [],
                \JSON_PRETTY_PRINT
            ),
            'text/html' => new HtmlResponse(
                HeaderUtil::parseOutputStream(WCF::getTPL()->fetchStream(
                    'offline',
                    'wcf',
                    [
                        'templateName' => 'offline',
                        'templateNameApplication' => 'wcf',
                    ]
                )),
                self::STATUS_CODE
            ),
        });
    }
}
