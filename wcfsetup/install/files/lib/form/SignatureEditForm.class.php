<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\signature\SignatureCache;
use wcf\system\WCF;

/**
 * Shows the signature edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class SignatureEditForm extends MessageForm {
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_USER_SIGNATURE');
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'signatureEdit';
	
	/**
	 * parsed signature cache
	 * @var	string
	 */
	public $signatureCache = null;
	
	/**
	 * @see	\wcf\form\MessageForm::$allowedBBCodesPermission
	 */
	public $allowedBBCodesPermission = 'user.signature.allowedBBCodes';
	
	/**
	 * @see	\wcf\form\MessageForm::$permissionCanUseSmilies
	 */
	public $permissionCanUseSmilies = 'user.signature.canUseSmilies';
	
	/**
	 * @see	\wcf\form\MessageForm::$permissionCanUseHtml
	 */
	public $permissionCanUseHtml = 'user.signature.canUseHtml';
	
	/**
	 * @see	\wcf\form\MessageForm::$permissionCanUseBBCodes
	 */
	public $permissionCanUseBBCodes = 'user.signature.canUseBBCodes';
	
	/**
	 * @see	\wcf\form\MessageForm::$showSignatureSetting
	 */
	public $showSignatureSetting = false;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get max text length
		$this->maxTextLength = WCF::getSession()->getPermission('user.signature.maxLength');
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		if (WCF::getUser()->disableSignature) throw new PermissionDeniedException();
		
		AbstractForm::validate();
		
		if (!empty($this->text)) {
			$this->validateText();
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (empty($_POST)) {
			$this->enableBBCodes = WCF::getUser()->signatureEnableBBCodes;
			$this->enableHtml = WCF::getUser()->signatureEnableHtml;
			$this->enableSmilies = WCF::getUser()->signatureEnableSmilies;
			$this->text = WCF::getUser()->signature;
			$this->preParse = true;
		}
		
		$this->signatureCache = SignatureCache::getInstance()->getSignature(WCF::getUser());
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'signatureCache' => $this->signatureCache
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.signature');
		
		parent::show();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserAction(array(WCF::getUser()), 'update', array(
			'data' => array_merge($this->additionalFields, array(
				'signature' => $this->text,
				'signatureEnableBBCodes' => $this->enableBBCodes,
				'signatureEnableHtml' => $this->enableHtml,
				'signatureEnableSmilies' => $this->enableSmilies
			))
		));
		$this->objectAction->executeAction();
		SignatureCache::getInstance()->getSignature(new User(WCF::getUser()->userID));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
