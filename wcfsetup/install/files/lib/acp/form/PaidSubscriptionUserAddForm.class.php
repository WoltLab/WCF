<?php
namespace wcf\acp\form;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Shows the user subscription add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PaidSubscriptionUserAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_PAID_SUBSCRIPTION');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * subscription id
	 * @var	integer
	 */
	public $subscriptionID = 0;
	
	/**
	 * subscription object
	 * @var	\wcf\data\paid\subscription\PaidSubscription
	 */
	public $subscription = null;
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
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
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->subscriptionID = intval($_REQUEST['id']);
		$this->subscription = new PaidSubscription($this->subscriptionID);
		if (!$this->subscription->subscriptionID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['endDate'])) $this->endDate = $_POST['endDate'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		$this->user = User::getUserByUsername($this->username);
		if (!$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		if ($this->subscription->subscrtionLength) {
			$this->endDateTime = \DateTime::createFromFormat('Y-m-d', $this->endDate, new \DateTimeZone('UTC'));
			if ($this->endDateTime === false || $this->endDateTime->getTimestamp() < TIME_NOW) {
				throw new UserInputException('endDate');
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$userSubscription = PaidSubscriptionUser::getSubscriptionUser($this->subscriptionID, $this->user->userID);
		$data = array();
		if ($this->subscription->subscrtionLength) {
			$data['endDate'] = $this->endDateTime->getTimestamp();
		}
		if ($userSubscription === null) {
			// create new subscription
			$action = new PaidSubscriptionUserAction(array(), 'create', array(
				'user' => $this->user,
				'subscription' => $this->subscription,
				'data' => $data
			));
			$returnValues = $action->executeAction();
			$userSubscription = $returnValues['returnValues'];
		}
		else {
			// extend existing subscription
			$action = new PaidSubscriptionUserAction(array($userSubscription), 'extend', array('data' => $data));
			$action->executeAction();
		}
		$this->saved();
		
		// reset values
		$this->username = $this->endDate = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			if ($this->subscription->subscriptionLength) {
				$d = DateUtil::getDateTimeByTimestamp(TIME_NOW);
				$d->add($this->subscription->getDateInterval());
				$this->endDate = $d->format('Y-m-d');
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'subscriptionID' => $this->subscriptionID,
			'subscription' => $this->subscription,
			'username' => $this->username,
			'endDate' => $this->endDate
		));
	}
}
