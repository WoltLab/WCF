<?php
namespace wcf\acp\form;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the user subscription edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
class PaidSubscriptionUserEditForm extends PaidSubscriptionUserAddForm {
	/**
	 * subscription user id
	 * @var	integer
	 */
	public $subscriptionUserID = 0;
	
	/**
	 * subscription user object
	 * @var	PaidSubscriptionUser
	 */
	public $subscriptionUser = null;
	
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
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['id'])) $this->subscriptionUserID = intval($_REQUEST['id']);
		$this->subscriptionUser = new PaidSubscriptionUser($this->subscriptionUserID);
		if (!$this->subscriptionUser->subscriptionUserID || !$this->subscriptionUser->endDate || !$this->subscriptionUser->isActive) {
			throw new IllegalLinkException();
		}
		$this->subscription = $this->subscriptionUser->getSubscription();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateUsername() {}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$data = [
			'endDate' => $this->endDateTime->getTimestamp()
		];
		$this->objectAction = new PaidSubscriptionUserAction([$this->subscriptionUser], 'update', [
			'data' => $data
		]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		AbstractForm::readData();
		
		if (empty($_POST)) {
			$d = DateUtil::getDateTimeByTimestamp($this->subscriptionUser->endDate);
			$this->endDate = $d->format('Y-m-d');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'subscriptionUserID' => $this->subscriptionUserID,
			'subscriptionUser' => $this->subscriptionUser,
			'action' => 'edit'
		]);
	}
}
