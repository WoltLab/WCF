<?php
namespace wcf\acp\form;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionAction;
use wcf\data\paid\subscription\PaidSubscriptionList;
use wcf\form\AbstractForm;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the paid subscription edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class PaidSubscriptionEditForm extends PaidSubscriptionAddForm {
	/**
	 * id of the edited subscription
	 * @var	integer
	 */
	public $subscriptionID = 0;
	
	/**
	 * edited subscription object
	 * @var	\wcf\data\paid\subscription\PaidSubscription
	 */
	public $subscription = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->subscriptionID = intval($_REQUEST['id']);
		$this->subscription = new PaidSubscription($this->subscriptionID);
		if (!$this->subscription->subscriptionID) {
			throw new PermissionDeniedException();
		}
		
		parent::readParameters();
	}
	
	protected function getAvailableSubscriptions() {
		$subscriptionList = new PaidSubscriptionList();
		$subscriptionList->getConditionBuilder()->add('subscriptionID <> ?', [$this->subscriptionID]);
		$subscriptionList->sqlOrderBy = 'title';
		$subscriptionList->readObjects();
		$this->availableSubscriptions = $subscriptionList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('description', 1, $this->subscription->description, 'wcf.paidSubscription.subscription\d+.description');
			I18nHandler::getInstance()->setOptions('title', 1, $this->subscription->title, 'wcf.paidSubscription.subscription\d+');
				
			$this->isDisabled = $this->subscription->isDisabled;
			$this->showOrder = $this->subscription->showOrder;
			$this->cost = $this->subscription->cost;
			$this->currency = $this->subscription->currency;
			$this->subscriptionLength = $this->subscription->subscriptionLength;
			$this->subscriptionLengthUnit = $this->subscription->subscriptionLengthUnit;
			$this->isRecurring = $this->subscription->isRecurring;
			$this->groupIDs = explode(',', $this->subscription->groupIDs);
			$this->excludedSubscriptionIDs = explode(',', $this->subscription->excludedSubscriptionIDs);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// read i18n values
		I18nHandler::getInstance()->readValues();
		
		// handle i18n plain input
		if (I18nHandler::getInstance()->isPlainValue('description')) $this->description = I18nHandler::getInstance()->getValue('description');
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		
		if (!empty($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['cost'])) $this->cost = floatval($_POST['cost']);
		if (isset($_POST['currency'])) $this->currency = $_POST['currency'];
		if (!empty($_POST['subscriptionLengthPermanent'])) $this->subscriptionLengthPermanent = 1;
		if (!$this->subscriptionLengthPermanent) {
			if (isset($_POST['subscriptionLength'])) $this->subscriptionLength = intval($_POST['subscriptionLength']);
			if (isset($_POST['subscriptionLengthUnit'])) $this->subscriptionLengthUnit = $_POST['subscriptionLengthUnit'];
		}
		if (!empty($_POST['isRecurring'])) $this->isRecurring = 1;
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// update description
		$this->description = 'wcf.paidSubscription.subscription'.$this->subscription->subscriptionID.'.description';
		if (I18nHandler::getInstance()->isPlainValue('description')) {
			I18nHandler::getInstance()->remove($this->description);
			$this->description = I18nHandler::getInstance()->getValue('description');
		}
		else {
			I18nHandler::getInstance()->save('description', $this->description, 'wcf.paidSubscription', 1);
		}
		
		// update title
		$this->title = 'wcf.paidSubscription.subscription'.$this->subscription->subscriptionID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($this->title);
			$this->title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $this->title, 'wcf.paidSubscription', 1);
		}
		
		// save subscription
		$this->objectAction = new PaidSubscriptionAction([$this->subscription], 'update', ['data' => array_merge($this->additionalFields, [
			'title' => $this->title,
			'description' => $this->description,
			'isDisabled' => $this->isDisabled,	
			'showOrder' => $this->showOrder,
			'cost' => $this->cost,
			'currency' => $this->currency,
			'subscriptionLength' => $this->subscriptionLength,
			'subscriptionLengthUnit' => $this->subscriptionLengthUnit,
			'isRecurring' => $this->isRecurring,
			'groupIDs' => implode(',', $this->groupIDs),
			'excludedSubscriptionIDs' => implode(',', $this->excludedSubscriptionIDs)
		])]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$useRequestData = (empty($_POST)) ? false : true;
		I18nHandler::getInstance()->assignVariables($useRequestData);
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'subscriptionID' => $this->subscriptionID,
			'subscription' => $this->subscription
		]);
	}
}
