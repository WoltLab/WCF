<?php

namespace wcf\acp\page;

use wcf\acp\action\FirstTimeSetupAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\page\AbstractPage;
use wcf\system\acp\dashboard\AcpDashboard;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the welcome page in admin control panel.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class IndexPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.general.canUseAcp'];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'dashboard' => new AcpDashboard(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // check package installation queue
        if (!\PACKAGE_ID && $this->action == 'WCFSetup') {
            $queue = new PackageInstallationQueue(1);

            \assert($queue->queueID === 1);
            \assert($queue->parentQueueID === 0);
            \assert($queue->package === 'com.woltlab.wcf');

            WCF::getTPL()->assign(['queueID' => $queue->queueID]);
            WCF::getTPL()->display('packageInstallationSetup');

            exit;
        }

        if (\FIRST_TIME_SETUP_STATE != -1) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(
                FirstTimeSetupAction::class,
            ));

            exit;
        }

        // show page
        parent::show();
    }
}
