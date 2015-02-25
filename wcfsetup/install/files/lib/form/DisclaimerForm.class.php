<?php
namespace wcf\form;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the disclaimer.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class DisclaimerForm extends AbstractForm {
	/**
	 * true, if the user has accepted the disclaimer
	 * @var	boolean
	 */
	public $accept = false;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// registration disabled
		if (!WCF::getUser()->userID && REGISTER_DISABLED) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!WCF::getUser()->userID && isset($_POST['accept'])) $this->accept = true;
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!$this->accept) throw new UserInputException('accept');
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		WCF::getSession()->register('disclaimerAccepted', true);
		$this->saved();
		WCF::getSession()->update();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Register'));
		exit;
	}
}
