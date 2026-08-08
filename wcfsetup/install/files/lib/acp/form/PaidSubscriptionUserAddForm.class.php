<?php

namespace wcf\acp\form;

use wcf\command\paid\subscription\user\ExtendPaidSubscriptionUser;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\http\Helper;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the user subscription add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionUserAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];

    /**
     * subscription object
     * @var ?PaidSubscription
     */
    public $subscription;

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * user object
     * @var ?User
     */
    public $user;

    /**
     * subscription end date
     * @var string
     */
    public $endDate = '';

    /**
     * subscription end date
     * @var ?\DateTime
     */
    public $endDateTime;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->subscription = Helper::fetchObjectFromQueryParameter(PaidSubscription::class);
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['username'])) {
            $this->username = StringUtil::trim($_POST['username']);
        }
        if (isset($_POST['endDate'])) {
            $this->endDate = $_POST['endDate'];
        }
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        $this->validateUsername();
        $this->validateEndDate();
    }

    /**
     * Validates given username.
     *
     * @return void
     * @throws UserInputException
     */
    protected function validateUsername()
    {
        if (empty($this->username)) {
            throw new UserInputException('username');
        }
        $this->user = User::getUserByUsername($this->username);
        if ($this->user->isGuest()) {
            throw new UserInputException('username', 'notFound');
        }
    }

    /**
     * Validates given end date.
     *
     * @return void
     * @throws UserInputException
     */
    protected function validateEndDate()
    {
        if ($this->subscription->subscriptionLength !== 0) {
            $this->endDateTime = \DateTime::createFromFormat('Y-m-d', $this->endDate, new \DateTimeZone('UTC'));
            if ($this->endDateTime === false || $this->endDateTime->getTimestamp() < \TIME_NOW) {
                throw new UserInputException('endDate');
            }
        }
    }

    #[\Override]
    public function save()
    {
        parent::save();

        $userSubscription = PaidSubscriptionUser::getSubscriptionUser(
            $this->subscription->subscriptionID,
            $this->user->userID
        );
        $data = [];
        if ($this->subscription->subscriptionLength !== 0) {
            $data['endDate'] = $this->endDateTime->getTimestamp();
        }
        if ($userSubscription === null) {
            // create new subscription
            $this->objectAction = new PaidSubscriptionUserAction([], 'create', [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'data' => $data,
            ]);
            $this->objectAction->executeAction();
        } else {
            new ExtendPaidSubscriptionUser(
                $userSubscription,
                $data['endDate'] ?? null,
            )();
        }
        $this->saved();

        // reset values
        $this->username = '';
        $this->setDefaultEndDate();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            $this->setDefaultEndDate();
        }
    }

    /**
     * Sets the default value for the end date.
     *
     * @return void
     */
    protected function setDefaultEndDate()
    {
        if ($this->subscription->subscriptionLength !== 0) {
            $d = DateUtil::getDateTimeByTimestamp(\TIME_NOW);
            $d->add($this->subscription->getDateInterval());
            $this->endDate = $d->format('Y-m-d');
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'subscriptionID' => $this->subscription->subscriptionID,
            'subscription' => $this->subscription,
            'username' => $this->username,
            'endDate' => $this->endDate,
            'action' => 'add',
        ]);
    }
}
