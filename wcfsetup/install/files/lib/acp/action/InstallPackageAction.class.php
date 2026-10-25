<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\application\Application;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Handles an AJAX-based package installation.
 *
 * The values of the forms that package installation plugins prompt for are
 * read from `$_REQUEST` by `FormDocument::handleRequest()`, therefore the
 * request body is form data and the parameters of this controller are passed
 * through the query string.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class InstallPackageAction implements RequestHandlerInterface
{
    private PackageInstallationDispatcher $installation;

    private PackageInstallationQueue $queue;

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        queueID: positive-int,
                        step: "install" | "prepare" | "rollback",
                        node?: string,
                        redirectLocation?: "license"
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->queue = new PackageInstallationQueue($parameters['queueID']);
        if ($this->queue->isNil()) {
            throw new IllegalLinkException();
        }

        $this->checkQueuePermissions();

        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            $this->installation = $this->createDispatcher($request, $this->queue);

            return match ($parameters['step']) {
                'prepare' => $this->stepPrepare(),
                'install' => $this->stepInstall($parameters['node'] ?? '', $parameters['redirectLocation'] ?? ''),
                'rollback' => $this->stepRollback(),
            };
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * Validates that the active user is allowed to process this queue.
     *
     * @since 6.2
     */
    protected function checkQueuePermissions(): void
    {
        // The Core is installed through this action while the WCFSetup is still
        // running, at which point there is neither a user nor any permission.
        if (\PACKAGE_ID === 0) {
            return;
        }

        if ($this->queue->action === 'install') {
            WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
        } else {
            WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);
        }

        if ($this->queue->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @since 6.3
     */
    protected function createDispatcher(
        ServerRequestInterface $request,
        PackageInstallationQueue $queue
    ): PackageInstallationDispatcher {
        return new PackageInstallationDispatcher($queue);
    }

    /**
     * Prepares the installation process.
     */
    protected function stepPrepare(): ResponseInterface
    {
        // update package information
        $this->installation->updatePackage();

        if ($this->installation->getAction() === 'update' && $this->queue->package === 'com.woltlab.wcf') {
            WCF::checkWritability();
        }

        $this->installation->nodeBuilder->purgeNodes();
        $this->installation->nodeBuilder->buildNodes();

        $nextNode = $this->installation->nodeBuilder->getNextNode();
        $queueID = $this->installation->nodeBuilder->getQueueByNode(
            $this->installation->queue->processNo,
            $nextNode
        );

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'packageInstallationStepPrepare', [
                'installationType' => $this->queue->action,
                'packageName' => $this->installation->queue->packageName,
            ]),
            'step' => 'install',
            'node' => $nextNode,
            'currentAction' => $this->getCurrentAction($queueID),
            'progress' => 0,
            'queueID' => $queueID,
        ]);
    }

    /**
     * Executes installation based upon nodes.
     */
    protected function stepInstall(string $node, string $redirectLocation): ResponseInterface
    {
        $step = $this->installation->install($node);
        $queueID = $this->installation->nodeBuilder->getQueueByNode(
            $this->installation->queue->processNo,
            $step->getNode()
        );

        if ($step->hasDocument()) {
            return new JsonResponse([
                'currentAction' => $this->getCurrentAction($queueID),
                'innerTemplate' => $step->getTemplate(),
                'node' => $step->getNode(),
                'progress' => $this->installation->nodeBuilder->calculateProgress($node),
                'step' => 'install',
                'queueID' => $queueID,
            ]);
        }

        if ($step->getNode() === '') {
            // show success
            return new JsonResponse([
                'currentAction' => $this->getCurrentAction(null),
                'progress' => 100,
                'redirectLocation' => $this->getRedirectLink($redirectLocation),
                'step' => 'success',
            ]);
        }

        // continue with next node
        return new JsonResponse([
            'currentAction' => $this->getCurrentAction($queueID),
            'step' => 'install',
            'node' => $step->getNode(),
            'progress' => $this->installation->nodeBuilder->calculateProgress($node),
            'queueID' => $queueID,
        ]);
    }

    /**
     * Returns the link to the page to which the user is redirected after
     * the installation finished.
     *
     * @since   5.2
     */
    protected function getRedirectLink(string $redirectLocation): string
    {
        // get domain path
        $sql = "SELECT  *
                FROM    wcf1_application
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);

        /** @var Application $application */
        $application = $statement->fetchObject(Application::class);

        $controller = 'package-list';
        if ((bool)WCF::getSession()->getVar('__wcfSetup_completed')) {
            $controller = 'first-time-setup';

            WCF::getSession()->unregister('__wcfSetup_completed');
        } elseif ($redirectLocation === 'license') {
            $controller = 'license';
        }

        // Do not use the LinkHandler here as it is sort of unreliable during WCFSetup.
        return $application->getPageURL() . "acp/index.php?{$controller}/";
    }

    /**
     * Sets the parameters required to perform a rollback.
     */
    protected function stepRollback(): ResponseInterface
    {
        return new JsonResponse([
            'packageID' => $this->queue->packageID,
            'step' => 'rollback',
        ]);
    }

    /**
     * Returns current action by queue id.
     */
    protected function getCurrentAction(?int $queueID): string
    {
        if ($queueID === null) {
            return WCF::getLanguage()->get(match ($this->queue->action) {
                'install' => 'wcf.acp.package.installation.step.install.success',
                'update' => 'wcf.acp.package.installation.step.update.success',
            });
        }

        $installationType = $this->installation->nodeBuilder->getInstallationTypeByQueue($queueID);

        return WCF::getLanguage()->getDynamicVariable(
            match ($installationType) {
                'install' => 'wcf.acp.package.installation.step.install',
                'update' => 'wcf.acp.package.installation.step.update',
            },
            ['packageName' => $this->installation->nodeBuilder->getPackageNameByQueue($queueID)]
        );
    }
}
