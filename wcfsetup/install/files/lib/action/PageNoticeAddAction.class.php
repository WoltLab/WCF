<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\pageNotice\CreatePageNotice;
use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\data\page\PageCache;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\PageNoticeDialogForm;
use wcf\system\WCF;

/**
 * Adds a notice to a page in a dialog.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeAddAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    pageID: positive-int,
                    pageObjectID?: positive-int
                }
                EOT
        );

        $page = PageCache::getInstance()->getPage($parameters['pageID']);
        if ($page === null) {
            throw new IllegalLinkException();
        }

        $pageObjectID = $page->requireObjectID !== 0 ? ($parameters['pageObjectID'] ?? null) : null;
        if (!PageNotice::canManage($page, $pageObjectID)) {
            throw new PermissionDeniedException();
        }

        $form = PageNoticeDialogForm::create(static::class);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $builder = PageNoticeBuilder::forCreate()
                ->setPage($page, $pageObjectID)
                ->setUser(WCF::getUser())
                ->setTime(\TIME_NOW);
            $form->applyValuesToBuilder($builder);

            new CreatePageNotice($builder)();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }
}
