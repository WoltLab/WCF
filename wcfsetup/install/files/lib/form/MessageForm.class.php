<?php
namespace wcf\form;
use wcf\data\language\Language;
use wcf\data\smiley\category\SmileyCategory;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\bbcode\PreParser;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\censorship\Censorship;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * MessageForm is an abstract form implementation for a message with optional captcha suppport.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class MessageForm extends AbstractCaptchaForm {
	/**
	 * name of the permission which contains the allowed BBCodes
	 * @var	string
	 */
	public $allowedBBCodesPermission = 'user.message.allowedBBCodes';
	
	/**
	 * attachment handler
	 * @var	\wcf\system\attachment\AttachmentHandler
	 */
	public $attachmentHandler = null;
	
	/**
	 * object id for attachments
	 * @var	integer
	 */
	public $attachmentObjectID = 0;
	
	/**
	 * object type for attachments, if left blank, attachment support is disabled
	 * @var	string
	 */
	public $attachmentObjectType = '';
	
	/**
	 * parent object id for attachments
	 * @var	integer
	 */
	public $attachmentParentObjectID = 0;
	
	/**
	 * list of available content languages
	 * @var	Language[]
	 */
	public $availableContentLanguages = array();
	
	/**
	 * list of default smilies
	 * @var	Smiley[]
	 */
	public $defaultSmilies = array();
	
	/**
	 * enables bbcodes
	 * @var	boolean
	 */
	public $enableBBCodes = 1;
	
	/**
	 * enables html
	 * @var	boolean
	 */
	public $enableHtml = 0;
	
	/**
	 * enables multilingualism
	 * @var	boolean
	 */
	public $enableMultilingualism = false;
	
	/**
	 * enables smilies
	 * @var	boolean
	 */
	public $enableSmilies = 1;
	
	/**
	 * content language id
	 * @var	integer
	 */
	public $languageID = null;
	
	/**
	 * maximum text length
	 * @var	integer
	 */
	public $maxTextLength = 0;
	
	/**
	 * pre parses the message
	 * @var	boolean
	 */
	public $preParse = 1;
	
	/**
	 * required permission to use BBCodes
	 * @var	boolean
	 */
	public $permissionCanUseBBCodes = 'user.message.canUseBBCodes';
	
	/**
	 * required permission to use HTML
	 * @var	boolean
	 */
	public $permissionCanUseHtml = 'user.message.canUseHtml';
	
	/**
	 * required permission to use smilies
	 * @var	boolean
	 */
	public $permissionCanUseSmilies = 'user.message.canUseSmilies';
	
	/**
	 * shows the signature
	 * @var	boolean
	 */
	public $showSignature = 0;
	
	/**
	 * enables the 'showSignature' setting
	 * @var	boolean
	 */
	public $showSignatureSetting = 1;
	
	/**
	 * list of smiley categories
	 * @var	SmileyCategory[]
	 */
	public $smileyCategories = array();
	
	/**
	 * message subject
	 * @var	string
	 */
	public $subject = '';
	
	/**
	 * message text
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * temp hash
	 * @var	string
	 */
	public $tmpHash = '';
	
	/**
	 * @see	\wcf\form\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['tmpHash'])) {
			$this->tmpHash = $_REQUEST['tmpHash'];
		}
		if (empty($this->tmpHash)) {
			$this->tmpHash = WCF::getSession()->getVar('__wcfAttachmentTmpHash');
			if ($this->tmpHash === null) {
				$this->tmpHash = StringUtil::getRandomID();
			}
			else {
				WCF::getSession()->unregister('__wcfAttachmentTmpHash');
			}
		}
		
		if ($this->enableMultilingualism) {
			$this->availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
			if (WCF::getUser()->userID) {
				foreach ($this->availableContentLanguages as $key => $value) {
					if (!in_array($key, WCF::getUser()->getLanguageIDs())) unset($this->availableContentLanguages[$key]);
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim(MessageUtil::stripCrap($_POST['subject']));
		if (isset($_POST['text'])) $this->text = StringUtil::trim(MessageUtil::stripCrap($_POST['text']));
		
		// settings
		$this->enableSmilies = $this->enableHtml = $this->enableBBCodes = $this->preParse = $this->showSignature = 0;
		if (isset($_POST['preParse'])) $this->preParse = intval($_POST['preParse']);
		if (isset($_POST['enableSmilies']) && WCF::getSession()->getPermission($this->permissionCanUseSmilies)) $this->enableSmilies = intval($_POST['enableSmilies']);
		if (isset($_POST['enableHtml']) && WCF::getSession()->getPermission($this->permissionCanUseHtml)) $this->enableHtml = intval($_POST['enableHtml']);
		if (isset($_POST['enableBBCodes']) && WCF::getSession()->getPermission($this->permissionCanUseBBCodes)) $this->enableBBCodes = intval($_POST['enableBBCodes']);
		if (isset($_POST['showSignature'])) $this->showSignature = intval($_POST['showSignature']);
		
		// multilingualism
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		// subject
		$this->validateSubject();
		
		// text
		$this->validateText();
		
		// multilingualism
		$this->validateContentLanguage();
		
		parent::validate();
	}
	
	/**
	 * Validates the message subject.
	 */
	protected function validateSubject() {
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
		
		if (mb_strlen($this->subject) > 255) {
			$this->subject = mb_substr($this->subject, 0, 255);
		}
		
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($this->subject);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('subject', 'censoredWordsFound');
			}
		}
	}
	
	/**
	 * Validates the message text.
	 */
	protected function validateText() {
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
		
		// check text length
		if ($this->maxTextLength != 0 && mb_strlen($this->text) > $this->maxTextLength) {
			throw new UserInputException('text', 'tooLong');
		}
		
		/*if ($this->enableBBCodes && $this->allowedBBCodesPermission) {
			$disallowedBBCodes = BBCodeParser::getInstance()->validateBBCodes($this->text, ArrayUtil::trim(explode(',', WCF::getSession()->getPermission($this->allowedBBCodesPermission))));
			if (!empty($disallowedBBCodes)) {
				WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
				throw new UserInputException('text', 'disallowedBBCodes');
			}
		}*/
		
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($this->text);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('text', 'censoredWordsFound');
			}
		}
	}
	
	/**
	 * Validates content language id.
	 */
	protected function validateContentLanguage() {
		if (!$this->languageID || !$this->enableMultilingualism || empty($this->availableContentLanguages)) {
			$this->languageID = null;
			return;
		}
		
		if (!isset($this->availableContentLanguages[$this->languageID])) {
			throw new UserInputException('languageID', 'notValid');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$htmlInputProcessor = new HtmlInputProcessor();
		$this->text = $htmlInputProcessor->process($this->text);
		
		// parse URLs
		/* TODO
		if ($this->preParse == 1) {
			// BBCodes are enabled
			if ($this->enableBBCodes) {
				if ($this->allowedBBCodesPermission) {
					$this->text = PreParser::getInstance()->parse($this->text, ArrayUtil::trim(explode(',', WCF::getSession()->getPermission($this->allowedBBCodesPermission))));
				}
				else {
					$this->text = PreParser::getInstance()->parse($this->text);
				}
			}
			// BBCodes are disabled, thus no allowed BBCodes
			else {
				$this->text = PreParser::getInstance()->parse($this->text, array());
			}
		}
		*/
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		// get attachments
		if (MODULE_ATTACHMENT && $this->attachmentObjectType) {
			$this->attachmentHandler = new AttachmentHandler($this->attachmentObjectType, $this->attachmentObjectID, $this->tmpHash, $this->attachmentParentObjectID);
		}
		
		if (empty($_POST)) {
			$this->enableBBCodes = (ENABLE_BBCODES_DEFAULT_VALUE && WCF::getSession()->getPermission($this->permissionCanUseBBCodes)) ? 1 : 0;
			$this->enableHtml = (ENABLE_HTML_DEFAULT_VALUE && WCF::getSession()->getPermission($this->permissionCanUseHtml)) ? 1 : 0;
			$this->enableSmilies = (ENABLE_SMILIES_DEFAULT_VALUE && WCF::getSession()->getPermission($this->permissionCanUseSmilies)) ? 1 : 0;
			$this->preParse = PRE_PARSE_DEFAULT_VALUE;
			$this->showSignature = SHOW_SIGNATURE_DEFAULT_VALUE;
			$this->languageID = WCF::getLanguage()->languageID;
		}
		
		parent::readData();
		
		// get default smilies
		if (MODULE_SMILEY) {
			$this->smileyCategories = SmileyCache::getInstance()->getVisibleCategories();
			
			$firstCategory = reset($this->smileyCategories);
			if ($firstCategory) {
				$this->defaultSmilies = SmileyCache::getInstance()->getCategorySmilies($firstCategory->categoryID ?: null);
			}
		}
		
		if ($this->enableBBCodes && $this->allowedBBCodesPermission) {
			BBCodeHandler::getInstance()->setAllowedBBCodes(explode(',', WCF::getSession()->getPermission($this->allowedBBCodesPermission)));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables();
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'attachmentHandler' => $this->attachmentHandler,
			'attachmentObjectID' => $this->attachmentObjectID,
			'attachmentObjectType' => $this->attachmentObjectType,
			'attachmentParentObjectID' => $this->attachmentParentObjectID,
			'availableContentLanguages' => $this->availableContentLanguages,
			'defaultSmilies' => $this->defaultSmilies,
			'enableBBCodes' => $this->enableBBCodes,
			'enableHtml' => $this->enableHtml,
			'enableSmilies' => $this->enableSmilies,
			'languageID' => ($this->languageID ?: 0),
			'maxTextLength' => $this->maxTextLength,
			'permissionCanUseBBCodes' => $this->permissionCanUseBBCodes,
			'permissionCanUseHtml' => $this->permissionCanUseHtml,
			'permissionCanUseSmilies' => $this->permissionCanUseSmilies,
			'preParse' => $this->preParse,
			'showSignature' => $this->showSignature,
			'showSignatureSetting' => $this->showSignatureSetting,
			'smileyCategories' => $this->smileyCategories,
			'subject' => $this->subject,
			'text' => $this->text,
			'tmpHash' => $this->tmpHash
		));
		
		if ($this->allowedBBCodesPermission) {
			WCF::getTPL()->assign('allowedBBCodes', explode(',', ArrayUtil::trim(WCF::getSession()->getPermission($this->allowedBBCodesPermission))));
		}
	}
}
