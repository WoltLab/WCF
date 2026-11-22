<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\pageNotice\UpdatePageNotice;
use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\PageNoticeDialogForm;

/**
 * Edits a page notice in a dialog.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeEditAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pageNotice = Helper::fetchObjectFromQueryParameter(PageNotice::class);
        if (!$pageNotice->canEdit()) {
            throw new PermissionDeniedException();
        }

        $form = PageNoticeDialogForm::create(static::class, $pageNotice, $request->getMethod() === 'GET');

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $builder = PageNoticeBuilder::forUpdate($pageNotice);
            $form->applyValuesToBuilder($builder);

            new UpdatePageNotice($builder)();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }
}
