<?php
namespace wcf\acp\form;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionAction;
use wcf\data\paid\subscription\PaidSubscriptionEditor;
use wcf\data\paid\subscription\PaidSubscriptionList;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the paid subscription add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PaidSubscriptionAddForm extends AbstractForm {
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
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'paidSubscriptionAdd';
	
	/**
	 * subscription title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * subscription description
	 * @var	string
	 */
	public $description = '';
	
	/**
	 * indicates if the subscription is disabled
	 * @var	boolean
	 */
	public $isDisabled = 0;
	
	/**
	 * subscription show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * subscription cost
	 * @var	double
	 */
	public $cost = 0.0;
	
	/**
	 * subscription currency
	 * @var	string
	 */
	public $currency = 'USD';
	
	/**
	 * indicates if the subscription is permanent
	 * @var	boolean
	 */
	public $subscriptionLengthPermanent = 0;
	
	/**
	 * subscription length
	 * @var	integer
	 */
	public $subscriptionLength = 0;
	
	/**
	 * subscription length unit
	 * @var	string
	 */
	public $subscriptionLengthUnit = '';
	
	/**
	 * indicates if the subscription is a recurring payment
	 * @var	boolean
	 */
	public $isRecurring = 0;
	
	/**
	 * list of group ids
	 * @var	array<intewer>
	 */
	public $groupIDs = array();
	
	/**
	 * list of excluded subscriptions
	 * @var	array<integer>
	 */
	public $excludedSubscriptionIDs = array();
	
	/**
	 * available user groups
	 * @var	array
	 */
	public $availableUserGroups = array();
	
	/**
	 * list of available currencies
	 * @var	array<string>
	 */
	public $availableCurrencies = array();
	
	/**
	 * list of available subscriptions
	 * @var	array
	 */
	public $availableSubscriptions = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('description');
		I18nHandler::getInstance()->register('title');
		
		// get available user groups
		$this->availableUserGroups = UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
		
		if (!count(PaymentMethodHandler::getInstance()->getPaymentMethods())) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.acp.paidSubscription.error.noPaymentMethods'));
		}
		
		// get available currencies
		foreach (PaymentMethodHandler::getInstance()->getPaymentMethods() as $paymentMethod) {
			$this->availableCurrencies = array_merge($this->availableCurrencies, $paymentMethod->getSupportedCurrencies());
		}
		$this->availableCurrencies = array_unique($this->availableCurrencies);
		sort($this->availableCurrencies);
		
		// get available subscriptions
		$this->getAvailableSubscriptions();
	}
	
	protected function getAvailableSubscriptions() {
		$subscriptionList = new PaidSubscriptionList();
		$subscriptionList->sqlOrderBy = 'title';
		$subscriptionList->readObjects();
		$this->availableSubscriptions = $subscriptionList->getObjects();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
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
		if (isset($_POST['subscriptionLength'])) $this->subscriptionLength = intval($_POST['subscriptionLength']);
		if (isset($_POST['subscriptionLengthUnit'])) $this->subscriptionLengthUnit = $_POST['subscriptionLengthUnit'];
		if (!empty($_POST['isRecurring'])) $this->isRecurring = 1;
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['excludedSubscriptionIDs']) && is_array($_POST['excludedSubscriptionIDs'])) $this->excludedSubscriptionIDs = ArrayUtil::toIntegerArray($_POST['excludedSubscriptionIDs']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}
		
		// validate description
		if (!I18nHandler::getInstance()->validateValue('description', false, true)) {
			throw new UserInputException('description');
		}
		
		// validate cost
		if ($this->cost < 0.01) {
			throw new UserInputException('cost');
		}
		// validate currency
		if (!in_array($this->currency, $this->availableCurrencies)) {
			throw new UserInputException('cost');
		}
		
		if (!$this->subscriptionLengthPermanent) {
			if ($this->subscriptionLength < 1) {
				throw new UserInputException('subscriptionLength');
			}
			if ($this->subscriptionLengthUnit != 'D' && $this->subscriptionLengthUnit != 'M' && $this->subscriptionLengthUnit != 'Y') {
				throw new UserInputException('subscriptionLength');
			}
			if (($this->subscriptionLengthUnit == 'D' && $this->subscriptionLength > 90) || ($this->subscriptionLengthUnit == 'M' && $this->subscriptionLength > 24) || ($this->subscriptionLengthUnit == 'Y' && $this->subscriptionLength > 5)) {
				throw new UserInputException('subscriptionLength', 'invalid');
			}
		}
		
		// validate group ids
		if (empty($this->groupIDs)) {
			throw new UserInputException('groupIDs');
		}
		foreach ($this->groupIDs as $groupID) {
			if (!isset($this->availableUserGroups[$groupID])) throw new UserInputException('groupIDs');
		}
		// validate excluded subscriptions
		foreach ($this->excludedSubscriptionIDs as $key => $subscriptionID) {
			if (!isset($this->availableSubscriptions[$subscriptionID])) unset($this->excludedSubscriptionIDs[$key]);
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save subscription
		$this->objectAction = new PaidSubscriptionAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
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
		))));
		$returnValues = $this->objectAction->executeAction();
		
		// save i18n values
		$this->saveI18nValue($returnValues['returnValues'], 'description');
		$this->saveI18nValue($returnValues['returnValues'], 'title');
		$this->saved();
		
		// reset values
		$this->title = $this->description = '';
		$this->isDisabled = $this->showOrder = $this->cost = $this->subscriptionLength = $this->isRecurring = 0;
		$this->currency = 'EUR';
		$this->groupIDs = array();
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * Saves i18n values.
	 * 
	 * @param	\wcf\data\paid\subscription\PaidSubscription		$subscription
	 * @param	string							$columnName
	 */
	public function saveI18nValue(PaidSubscription $subscription, $columnName) {
		if (!I18nHandler::getInstance()->isPlainValue($columnName)) {
			I18nHandler::getInstance()->save($columnName, 'wcf.paidSubscription.subscription'.$subscription->subscriptionID.($columnName == 'description' ? '.description' : ''), 'wcf.paidSubscription', 1);
			
			// update database
			$editor = new PaidSubscriptionEditor($subscription);
			$editor->update(array(
				$columnName => 'wcf.paidSubscription.subscription'.$subscription->subscriptionID.($columnName == 'description' ? '.description' : '')
			));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'isDisabled' => $this->isDisabled,
			'showOrder' => $this->showOrder,
			'cost' => $this->cost,
			'currency' => $this->currency,
			'subscriptionLength' => $this->subscriptionLength,
			'subscriptionLengthUnit' => $this->subscriptionLengthUnit,
			'isRecurring' => $this->isRecurring,
			'groupIDs' => $this->groupIDs,
			'excludedSubscriptionIDs' => $this->excludedSubscriptionIDs,
			'availableCurrencies' => $this->availableCurrencies,
			'availableUserGroups' => $this->availableUserGroups,
			'availableSubscriptions' => $this->availableSubscriptions
		));
	}
}
