<?php

namespace wcf\acp\page;

use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows transaction details.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionTransactionLogPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];

    /**
     * log entry id
     * @var int
     */
    public $logID = 0;

    /**
     * log entry object
     * @var PaidSubscriptionTransactionLog
     */
    public $log;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->logID = \intval($_REQUEST['id']);
        }
        $this->log = new PaidSubscriptionTransactionLog($this->logID);
        if ($this->log->isNil()) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'logID' => $this->logID,
            'log' => $this->log,
        ]);
    }
}
