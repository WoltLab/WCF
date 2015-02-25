<?php
namespace wcf\form;
use wcf\data\user\UserProfile;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the user mail form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class MailForm extends AbstractCaptchaForm {
	/**
	 * @see	\wcf\form\AbstractCaptchaForm::$useCaptcha
	 */
	public $useCaptcha = PROFILE_MAIL_USE_CAPTCHA;
	
	/**
	 * recipient's user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * recipient's user object
	 * @var	\wcf\data\user\UserProfile
	 */
	public $user = 0;
	
	/**
	 * true to add the reply-to header
	 * @var	boolean
	 */
	public $showAddress = true;
	
	/**
	 * email subject
	 * @var	string
	 */
	public $subject = '';
	
	/**
	 * email message
	 * @var	string
	 */
	public $message = '';
	
	/**
	 * sender's email address
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		$this->user = UserProfile::getUserProfile($this->userID);
		if ($this->user === null) {
			throw new IllegalLinkException();
		}
		// validate ignore status
		if (WCF::getUser()->userID && $this->user->isIgnoredUser(WCF::getUser()->userID)) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('Mail', array('object' => $this->user->getDecoratedObject()));
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->showAddress = 0;
		if (isset($_POST['message'])) $this->message = StringUtil::trim($_POST['message']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['showAddress'])) $this->showAddress = intval($_POST['showAddress']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		if (!WCF::getUser()->userID) {
			if (empty($this->email)) {
				throw new UserInputException('email');
			}
			
			if (!UserUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'notValid');
			}
		}
		
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
		
		if (empty($this->message)) {
			throw new UserInputException('message');
		}
		
		parent::validate();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get recipient's language
		$userLanguage = $this->user->getLanguage();
		
		// build message data
		$subjectData = array(
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
			'subject' => $this->subject
		);
		$messageData = array(
			'message' => $this->message,
			'recipient' => $this->user,
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
		);
		
		// build mail
		$mail = new Mail(array($this->user->username => $this->user->email), $userLanguage->getDynamicVariable('wcf.user.mail.mail.subject', $subjectData), $userLanguage->getDynamicVariable('wcf.user.mail.mail', $messageData));
		$mail->setLanguage($userLanguage);
		
		// add reply-to tag
		if (WCF::getUser()->userID) {
			if ($this->showAddress) $mail->setHeader('Reply-To: '.Mail::buildAddress(WCF::getUser()->username, WCF::getUser()->email));
		}
		else {
			$mail->setHeader('Reply-To: '.$this->email);
		}
		
		// send mail
		$mail->send();
		$this->saved();
		
		// forward to profile page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink('User', array('object' => $this->user)), WCF::getLanguage()->getDynamicVariable('wcf.user.mail.sent', array('user' => $this->user)));
		exit;
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		WCF::getBreadcrumbs()->add($this->user->getBreadcrumb());
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'user' => $this->user,
			'showAddress' => $this->showAddress,
			'message' => $this->message,
			'subject' => $this->subject,
			'email' => $this->email
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		WCF::getSession()->checkPermissions(array('user.profile.canMail'));
		
		if (!$this->user->isAccessible('canMail')) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
