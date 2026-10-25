<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\page\PackageListPage;
use wcf\command\package\installation\queue\CancelPackageInstallation;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Cancels a package installation from its confirmation page.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PackageInstallationCancelAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $queue = new PackageInstallationQueue($parameters['id']);
        if ($queue->isNil() || $queue->parentQueueID !== 0 || $queue->done !== 0) {
            throw new IllegalLinkException();
        }

        if ($queue->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            new CancelPackageInstallation($queue)();

            return new JsonResponse([
                'redirectUrl' => LinkHandler::getInstance()->getControllerLink(PackageListPage::class),
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }
}
