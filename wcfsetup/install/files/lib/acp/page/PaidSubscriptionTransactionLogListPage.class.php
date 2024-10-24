<?php

namespace wcf\acp\page;

use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLogList;
use wcf\page\SortablePage;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of paid subscription transactions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    PaidSubscriptionTransactionLogList $objectList
 */
class PaidSubscriptionTransactionLogListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.transactionLog.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'logTime';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'logID',
        'subscriptionUserID',
        'userID',
        'subscriptionID',
        'paymentMethodObjectTypeID',
        'logTime',
        'transactionID',
        'logMessage',
    ];

    /**
     * @inheritDoc
     */
    public $objectListClassName = PaidSubscriptionTransactionLogList::class;

    /**
     * transaction id
     * @var string
     */
    public $transactionID = '';

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * subscription id
     * @var int
     */
    public $subscriptionID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['transactionID'])) {
            $this->transactionID = StringUtil::trim($_REQUEST['transactionID']);
        }
        if (isset($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (isset($_REQUEST['subscriptionID'])) {
            $this->subscriptionID = \intval($_REQUEST['subscriptionID']);
        }
    }

    /**
     * Initializes DatabaseObjectList instance.
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if ($this->transactionID) {
            $this->objectList->getConditionBuilder()->add(
                'paid_subscription_transaction_log.transactionID LIKE ?',
                ['%' . $this->transactionID . '%']
            );
        }
        if ($this->username) {
            $this->objectList->getConditionBuilder()->add(
                'paid_subscription_transaction_log.userID IN (
                    SELECT  userID
                    FROM    wcf1_user
                    WHERE   username LIKE ?
                )',
                ['%' . $this->username . '%']
            );
        }
        if ($this->subscriptionID) {
            $this->objectList->getConditionBuilder()->add(
                'paid_subscription_transaction_log.subscriptionID = ?',
                [$this->subscriptionID]
            );
        }

        $this->objectList->sqlSelects = 'user_table.username, paid_subscription.title';
        $this->objectList->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = paid_subscription_transaction_log.userID
            LEFT JOIN   wcf1_paid_subscription paid_subscription
            ON          paid_subscription.subscriptionID = paid_subscription_transaction_log.subscriptionID";
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'transactionID' => $this->transactionID,
            'username' => $this->username,
            'subscriptionID' => $this->subscriptionID,
            'availableSubscriptions' => PaidSubscriptionCacheBuilder::getInstance()->getData(),
        ]);
    }
}
