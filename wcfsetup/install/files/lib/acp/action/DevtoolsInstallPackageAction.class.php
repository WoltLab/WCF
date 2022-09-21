<?php

namespace wcf\acp\action;

use wcf\action\AbstractSecureAction;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package installation of devtools projects.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Action
 * @since   5.2
 */
final class DevtoolsInstallPackageAction extends InstallPackageAction
{
    /**
     * project whose source is installed as a package
     */
    public DevtoolsProject $project;

    /**
     * @inheritDoc
     */
    protected function getRedirectLink()
    {
        return LinkHandler::getInstance()->getLink('DevtoolsProjectList');
    }

    /**
     * @inheritDoc
     * @throws  IllegalLinkException
     */
    public function readParameters()
    {
        AbstractSecureAction::readParameters();

        if (isset($_REQUEST['step'])) {
            $this->step = StringUtil::trim($_REQUEST['step']);

            switch ($this->step) {
                case 'install':
                case 'prepare':
                case 'rollback':
                    // valid steps
                    break;

                default:
                    throw new IllegalLinkException();
                    break;
            }
        }

        if (isset($_POST['projectID'])) {
            $this->project = new DevtoolsProject(\intval($_POST['projectID']));
        }

        if (!isset($this->project) || !$this->project->projectID) {
            throw new IllegalLinkException();
        }

        if (isset($_POST['node'])) {
            $this->node = StringUtil::trim($_POST['node']);
        }

        if (isset($_POST['queueID'])) {
            $queueID = \intval($_POST['queueID']);
            $this->queue = new PackageInstallationQueue($queueID);
        }

        if (!isset($this->queue) || !$this->queue->queueID) {
            throw new IllegalLinkException();
        }

        $this->installation = new DevtoolsPackageInstallationDispatcher($this->project, $this->queue);
    }
}
