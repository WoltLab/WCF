<?php
namespace wcf\acp\form;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the user subscription add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class PaidSubscriptionUserAddForm extends AbstractForm {
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
	 * subscription id
	 * @var	integer
	 */
	public $subscriptionID = 0;
	
	/**
	 * subscription object
	 * @var	PaidSubscription
	 */
	public $subscription = null;
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * user object
	 * @var	User
	 */
	public $user = null;
	
	/**
	 * subscription end date
	 * @var	string
	 */
	public $endDate = '';
	
	/**
	 * subscription end date
	 * @var	\DateTime
	 */
	public $endDateTime = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->subscriptionID = intval($_REQUEST['id']);
		$this->subscription = new PaidSubscription($this->subscriptionID);
		if (!$this->subscription->subscriptionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['endDate'])) $this->endDate = $_POST['endDate'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->validateUsername();
		$this->validateEndDate();
	}
	
	/**
	 * Validates given username.
	 * 
	 * @throws UserInputException
	 */
	protected function validateUsername() {
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		$this->user = User::getUserByUsername($this->username);
		if (!$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
	}
	
	/**
	 * Validates given end date.
	 *
	 * @throws UserInputException
	 */
	protected function validateEndDate() {
		if ($this->subscription->subscriptionLength) {
			$this->endDateTime = \DateTime::createFromFormat('Y-m-d', $this->endDate, new \DateTimeZone('UTC'));
			if ($this->endDateTime === false || $this->endDateTime->getTimestamp() < TIME_NOW) {
				throw new UserInputException('endDate');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$userSubscription = PaidSubscriptionUser::getSubscriptionUser($this->subscriptionID, $this->user->userID);
		$data = [];
		if ($this->subscription->subscriptionLength) {
			$data['endDate'] = $this->endDateTime->getTimestamp();
		}
		if ($userSubscription === null) {
			// create new subscription
			$this->objectAction = new PaidSubscriptionUserAction([], 'create', [
				'user' => $this->user,
				'subscription' => $this->subscription,
				'data' => $data
			]);
			$this->objectAction->executeAction();
		}
		else {
			// extend existing subscription
			$this->objectAction = new PaidSubscriptionUserAction([$userSubscription], 'extend', ['data' => $data]);
			$this->objectAction->executeAction();
		}
		$this->saved();
		
		// reset values
		$this->username = '';
		$this->setDefaultEndDate();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->setDefaultEndDate();
		}
	}
	
	/**
	 * Sets the default value for the end date.
	 */
	protected function setDefaultEndDate() {
		if ($this->subscription->subscriptionLength) {
			$d = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$d->add($this->subscription->getDateInterval());
			$this->endDate = $d->format('Y-m-d');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'subscriptionID' => $this->subscriptionID,
			'subscription' => $this->subscription,
			'username' => $this->username,
			'endDate' => $this->endDate,
			'action' => 'add'
		]);
	}
}
