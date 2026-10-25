<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\page\PackageListPage;
use wcf\command\application\MarkApplicationAsTainted;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\http\Helper;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Handles an AJAX-based package uninstallation.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UninstallPackageAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        step: "prepare" | "uninstall",
                        packageID?: positive-int,
                        queueID?: positive-int,
                        node?: string
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            return match ($parameters['step']) {
                'prepare' => $this->stepPrepare(
                    $parameters['packageID'] ?? throw new IllegalLinkException()
                ),
                'uninstall' => $this->stepUninstall(
                    $parameters['queueID'] ?? throw new IllegalLinkException(),
                    $parameters['node'] ?? ''
                ),
            };
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * Prepares the uninstallation process.
     */
    private function stepPrepare(int $packageID): ResponseInterface
    {
        $package = new Package($packageID);
        if ($package->isNil() || !$package->canUninstall()) {
            throw new IllegalLinkException();
        }

        // get new process no
        $processNo = PackageInstallationQueue::getNewProcessNo();

        // create queue
        $queue = PackageInstallationQueueEditor::create([
            'processNo' => $processNo,
            'userID' => WCF::getUser()->userID,
            'packageName' => $package->getName(),
            'packageID' => $package->packageID,
            'action' => 'uninstall',
        ]);

        // initialize uninstallation
        $installation = new PackageUninstallationDispatcher($queue);

        // mark package as tainted if it is an app
        if ($package->isApplication !== 0) {
            $application = ApplicationHandler::getInstance()->getApplicationByID($package->packageID);
            \assert($application !== null);

            new MarkApplicationAsTainted($application)();
        }

        $installation->nodeBuilder->purgeNodes();
        $installation->nodeBuilder->buildNodes();

        $nextNode = $installation->nodeBuilder->getNextNode();
        $queueID = $installation->nodeBuilder->getQueueByNode(
            $queue->processNo,
            $nextNode
        );
        \assert($queueID !== null);

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'packageUninstallationStepPrepare', [
                'queue' => $queue,
            ]),
            'step' => 'uninstall',
            'node' => $nextNode,
            'currentAction' => WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.package.uninstallation.step.uninstall',
                [
                    'packageName' => $installation->nodeBuilder->getPackageNameByQueue($queueID),
                    'queue' => $queue,
                ]
            ),
            'progress' => 0,
            'queueID' => $queueID,
        ]);
    }

    /**
     * Uninstalls node components.
     */
    private function stepUninstall(int $queueID, string $node): ResponseInterface
    {
        $queue = new PackageInstallationQueue($queueID);
        if ($queue->isNil()) {
            throw new IllegalLinkException();
        }

        // `stepPrepare()` is guarded by `Package::canUninstall()`, but this step
        // resumes an existing queue and is reached without passing through it.
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        if ($queue->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        $installation = new PackageUninstallationDispatcher($queue);

        $step = $installation->uninstall($node);
        $queueID = $installation->nodeBuilder->getQueueByNode(
            $installation->queue->processNo,
            $step->getNode()
        );

        if ($step->getNode() === '') {
            return new JsonResponse([
                'currentAction' => WCF::getLanguage()->get('wcf.acp.package.uninstallation.step.success'),
                'progress' => 100,
                'redirectLocation' => LinkHandler::getInstance()->getControllerLink(
                    PackageListPage::class
                ),
                'step' => 'success',
            ]);
        }

        return new JsonResponse([
            'step' => 'uninstall',
            'node' => $step->getNode(),
            'progress' => $installation->nodeBuilder->calculateProgress($node),
            'queueID' => $queueID,
        ]);
    }
}
