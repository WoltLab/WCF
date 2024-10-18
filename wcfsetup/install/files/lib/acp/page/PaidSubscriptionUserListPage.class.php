<?php

namespace wcf\acp\page;

use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\page\SortablePage;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of paid subscription users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    PaidSubscriptionUserList $objectList
 */
class PaidSubscriptionUserListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.user.list';

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
    public $defaultSortField = 'username';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['subscriptionUserID', 'username', 'subscriptionID', 'startDate', 'endDate'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = PaidSubscriptionUserList::class;

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

        if ($this->username) {
            $this->objectList->getConditionBuilder()->add(
                'paid_subscription_user.userID IN (
                    SELECT  userID
                    FROM    wcf1_user
                    WHERE   username LIKE ?
                )',
                ['%' . $this->username . '%']
            );
        }
        if ($this->subscriptionID) {
            $this->objectList->getConditionBuilder()->add(
                'paid_subscription_user.subscriptionID = ?',
                [$this->subscriptionID]
            );
        }

        $this->objectList->getConditionBuilder()->add('paid_subscription_user.isActive = ?', [1]);
        $this->objectList->sqlSelects = 'user_table.username, paid_subscription.title';
        $this->objectList->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = paid_subscription_user.userID
            LEFT JOIN   wcf1_paid_subscription paid_subscription
            ON          paid_subscription.subscriptionID = paid_subscription_user.subscriptionID";
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        if ($this->sortField == 'username') {
            $this->sqlOrderBy = 'user_table.username ' . $this->sortOrder . ', paid_subscription_user.subscriptionUserID ' . $this->sortOrder;
        }

        parent::readObjects();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'username' => $this->username,
            'subscriptionID' => $this->subscriptionID,
            'availableSubscriptions' => PaidSubscriptionCacheBuilder::getInstance()->getData(),
        ]);
    }
}
