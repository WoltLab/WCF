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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class SignatureEditForm extends MessageForm {
	/**
	 * @inheritDoc
	 */
	public $disallowedBBCodesPermission = 'user.signature.disallowedBBCodes';
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $messageObjectType = 'com.woltlab.wcf.user.signature';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_USER_SIGNATURE'];
	
	/**
	 * @inheritDoc
	 */
	public $showSignatureSetting = false;
	
	/**
	 * parsed signature cache
	 * @var	string
	 */
	public $signatureCache;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'signatureEdit';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get max text length
		$this->maxTextLength = WCF::getSession()->getPermission('user.signature.maxLength');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (WCF::getUser()->disableSignature) throw new PermissionDeniedException();
		
		AbstractForm::validate();
		
		if (!empty($this->text)) {
			$this->validateText();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (empty($_POST)) {
			$this->text = WCF::getUser()->signature;
		}
		
		$this->signatureCache = SignatureCache::getInstance()->getSignature(WCF::getUser());
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'signatureCache' => $this->signatureCache
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.signature');
		
		parent::show();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new UserAction([WCF::getUser()], 'update', [
			'data' => array_merge($this->additionalFields, [
				'signature' => $this->htmlInputProcessor->getHtml(),
				'signatureEnableHtml' => 1
			])
		]);
		$this->objectAction->executeAction();
		SignatureCache::getInstance()->getSignature(new User(WCF::getUser()->userID));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
