<?php

namespace wcf\data\package\installation\queue;

use wcf\acp\page\PackageListPage;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\Package;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes package installation queue-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PackageInstallationQueue, PackageInstallationQueueEditor>
 */
class PackageInstallationQueueAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageInstallationQueueEditor::class;

    /**
     * queue of the canceled installation
     * @var ?PackageInstallationQueueEditor
     */
    protected $queue;

    /**
     * package the prepared queue belongs to
     * @var ?Package
     */
    protected $package;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['cancelInstallation'];

    /**
     * Validates the 'cancelInstallation' action.
     *
     * @return void
     */
    public function validateCancelInstallation()
    {
        // check permissions
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        // validate queue
        $this->queue = $this->getSingleObject();
        if ($this->queue->parentQueueID || $this->queue->done) {
            throw new UserInputException('objectIDs');
        }

        if ($this->queue->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Cancels a certain installation.
     *
     * @return array{url: string}
     */
    public function cancelInstallation()
    {
        @\unlink($this->queue->archive);

        $this->queue->delete();

        return [
            'url' => LinkHandler::getInstance()->getControllerLink(PackageListPage::class),
        ];
    }
}
