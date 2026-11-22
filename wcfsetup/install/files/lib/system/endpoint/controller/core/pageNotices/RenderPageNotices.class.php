<?php

namespace wcf\system\endpoint\controller\core\pageNotices;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\page\notice\PageNotice;
use wcf\data\page\PageCache;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\notice\PageNoticeHandler;

/**
 * Renders the notices of a page and object that are visible to the active
 * user, used to refresh the notices after they have been changed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/page-notices/render')]
final class RenderPageNotices implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderPageNoticesParameters::class);

        $page = PageCache::getInstance()->getPage($parameters->pageID);
        if ($page === null) {
            throw new IllegalLinkException();
        }

        $pageObjectID = $page->requireObjectID !== 0 ? $parameters->pageObjectID : null;
        if (!PageNotice::canAccessPage($page, $pageObjectID)) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => PageNoticeHandler::getInstance()->renderNotices($page, $pageObjectID),
        ]);
    }
}

/** @internal */
final class RenderPageNoticesParameters
{
    public function __construct(
        /** @var positive-int */
        public readonly int $pageID,

        /** @var ?positive-int */
        public readonly ?int $pageObjectID = null,
    ) {}
}
