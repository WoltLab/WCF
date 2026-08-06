<?php

namespace wcf\acp\page;

use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog;
use wcf\http\Helper;
use wcf\page\AbstractPage;
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
     * log entry object
     * @var ?PaidSubscriptionTransactionLog
     */
    public $log;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->log = Helper::fetchObjectFromQueryParameter(PaidSubscriptionTransactionLog::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'logID' => $this->log->logID,
            'log' => $this->log,
        ]);
    }
}
