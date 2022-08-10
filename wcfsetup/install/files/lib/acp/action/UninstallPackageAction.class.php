<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use wcf\acp\page\PackageListPage;
use wcf\action\AbstractSecureAction;
use wcf\data\application\ApplicationAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package uninstallation.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Action
 */
final class UninstallPackageAction extends AbstractSecureAction
{
    public string $step = '';

    public string $node = '';

    public PackageUninstallationDispatcher $installation;

    public PackageInstallationQueue $queue;

    protected int $packageID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['step'])) {
            $this->step = StringUtil::trim($_REQUEST['step']);
        }

        switch ($this->step) {
            case 'prepare':
            case 'uninstall':
                // valid steps
                break;

            default:
                throw new IllegalLinkException();
                break;
        }

        if (isset($_POST['node'])) {
            $this->node = StringUtil::trim($_POST['node']);
        }

        if (isset($_POST['packageID'])) {
            $this->packageID = \intval($_POST['packageID']);
        } else {
            if (isset($_POST['queueID'])) {
                $this->queue = new PackageInstallationQueue(\intval($_POST['queueID']));
            }
    
            if (!isset($this->queue) || !$this->queue->queueID) {
                throw new IllegalLinkException();
            }

            $this->installation = new PackageUninstallationDispatcher($this->queue);
        }
    }

    public function execute()
    {
        parent::execute();

        $methodName = 'step' . StringUtil::firstCharToUpperCase($this->step);

        $response = $this->{$methodName}();

        $this->executed();

        return $response;
    }

    /**
     * Prepares the uninstallation process.
     */
    protected function stepPrepare(): ResponseInterface
    {
        $package = new Package($this->packageID);
        if (!$package->packageID || !$package->canUninstall()) {
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
        $this->installation = new PackageUninstallationDispatcher($queue);

        // mark package as tainted if it is an app
        if ($package->isApplication) {
            $applicationAction = new ApplicationAction([$package->packageID], 'markAsTainted');
            $applicationAction->executeAction();
        }

        $this->installation->nodeBuilder->purgeNodes();
        $this->installation->nodeBuilder->buildNodes();

        $nextNode = $this->installation->nodeBuilder->getNextNode();
        $queueID = $this->installation->nodeBuilder->getQueueByNode(
            $queue->processNo,
            $nextNode
        );

        WCF::getTPL()->assign([
            'queue' => $queue,
        ]);

        return new JsonResponse([
            'template' => WCF::getTPL()->fetch('packageUninstallationStepPrepare'),
            'step' => 'uninstall',
            'node' => $nextNode,
            'currentAction' => $this->getCurrentAction($queueID),
            'progress' => 0,
            'queueID' => $queueID,
        ]);
    }

    /**
     * Uninstalls node components.
     */
    public function stepUninstall(): ResponseInterface
    {
        $step = $this->installation->uninstall($this->node);
        $queueID = $this->installation->nodeBuilder->getQueueByNode(
            $this->installation->queue->processNo,
            $step->getNode()
        );

        if ($step->getNode() == '') {
            $this->installation->nodeBuilder->purgeNodes();
            $this->finalize();

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
            'progress' => $this->installation->nodeBuilder->calculateProgress($this->node),
            'queueID' => $queueID,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAction($queueID)
    {
        if ($queueID === null) {
            // success message
            $currentAction = WCF::getLanguage()->get('wcf.acp.package.uninstallation.step.' . $this->queue->action . '.success');
        } else {
            // build package name
            $packageName = $this->installation->nodeBuilder->getPackageNameByQueue($queueID);
            $installationType = $this->installation->nodeBuilder->getInstallationTypeByQueue($queueID);
            $currentAction = WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.package.uninstallation.step.' . $installationType,
                ['packageName' => $packageName]
            );
        }

        return $currentAction;
    }

    /**
     * Clears resources after successful uninstallation.
     */
    protected function finalize()
    {
        // create search index tables
        SearchIndexManager::getInstance()->createSearchIndices();

        VersionTracker::getInstance()->createStorageTables();

        CacheHandler::getInstance()->flushAll();
    }
}
