<?php
namespace wcf\form;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\UserMailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the user mail form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class MailForm extends AbstractCaptchaForm {
	/**
	 * @inheritDoc
	 */
	public $useCaptcha = PROFILE_MAIL_USE_CAPTCHA;
	
	/**
	 * @inheritDoc
	 */
	public $forceCaptcha = true;
	
	/**
	 * recipient's user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * recipient's user object
	 * @var	UserProfile
	 */
	public $user = null;
	
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
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.canMail'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		$this->user = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
		if ($this->user === null) {
			throw new IllegalLinkException();
		}
		// validate ignore status
		if (WCF::getUser()->userID && $this->user->isIgnoredUser(WCF::getUser()->userID)) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('Mail', ['object' => $this->user->getDecoratedObject()]);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function validate() {
		if (!WCF::getUser()->userID) {
			if (empty($this->email)) {
				throw new UserInputException('email');
			}
			
			if (!UserUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'invalid');
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// build message data
		$messageData = [
			'message' => $this->message,
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email
		];
		
		// build mail
		$email = new Email();
		$email->addRecipient(new UserMailbox($this->user->getDecoratedObject()));
		$email->setSubject($this->user->getLanguage()->getDynamicVariable('wcf.user.mail.mail.subject', [
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
			'subject' => $this->subject
		]));
		$email->setBody(new MimePartFacade([
			new RecipientAwareTextMimePart('text/html', 'email_mail', 'wcf', $messageData),
			new RecipientAwareTextMimePart('text/plain', 'email_mail', 'wcf', $messageData)
		]));
		
		// add reply-to tag
		if (WCF::getUser()->userID) {
			if ($this->showAddress) {
				$email->setReplyTo(new UserMailbox(WCF::getUser()));
			}
		}
		else {
			$email->setReplyTo(new Mailbox($this->email));
		}
		
		// send mail
		$email->send();
		$this->saved();
		
		// forward to profile page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink('User', ['object' => $this->user]), WCF::getLanguage()->getDynamicVariable('wcf.user.mail.sent', ['user' => $this->user]));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.User', $this->user->userID, $this->user);
		if (MODULE_MEMBERS_LIST) PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'user' => $this->user,
			'showAddress' => $this->showAddress,
			'message' => $this->message,
			'subject' => $this->subject,
			'email' => $this->email
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		if (!$this->user->isAccessible('canMail')) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
