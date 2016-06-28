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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
abstract class MessageForm extends AbstractCaptchaForm {
	/**
	 * name of the permission which contains the allowed BBCodes
	 * @var	string
	 */
	public $allowedBBCodesPermission = 'user.message.allowedBBCodes';
	
	/**
	 * attachment handler
	 * @var	AttachmentHandler
	 */
	public $attachmentHandler;
	
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
	public $availableContentLanguages = [];
	
	/**
	 * list of default smilies
	 * @var	Smiley[]
	 */
	public $defaultSmilies = [];
	
	/**
	 * enables multilingualism
	 * @var	boolean
	 */
	public $enableMultilingualism = false;
	
	/**
	 * @var HtmlInputProcessor
	 */
	public $htmlInputProcessor;
	
	/**
	 * content language id
	 * @var	integer
	 */
	public $languageID;
	
	/**
	 * maximum text length
	 * @var	integer
	 */
	public $maxTextLength = 0;
	
	/**
	 * message object type for html processing
	 * @var string
	 */
	public $messageObjectType = '';
	
	/**
	 * list of smiley categories
	 * @var	SmileyCategory[]
	 */
	public $smileyCategories = [];
	
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim(MessageUtil::stripCrap($_POST['subject']));
		if (isset($_POST['text'])) $this->text = StringUtil::trim(MessageUtil::stripCrap($_POST['text']));
		
		// multilingualism
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @inheritDoc
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
		if (empty($this->messageObjectType)) {
			throw new \RuntimeException("Expected non-empty message object type for '".get_class($this)."'");
		}
		
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
		
		// check text length
		if ($this->maxTextLength != 0 && mb_strlen($this->text) > $this->maxTextLength) {
			throw new UserInputException('text', 'tooLong');
		}
		
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($this->text, $this->messageObjectType, 0);
		
		// TODO: add checks for disallowed bbcodes and stuff
		$this->htmlInputProcessor->validate();
		
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->text = $this->htmlInputProcessor->getHtml();
		
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
	 * @inheritDoc
	 */
	public function readData() {
		// get attachments
		if (MODULE_ATTACHMENT && $this->attachmentObjectType) {
			$this->attachmentHandler = new AttachmentHandler($this->attachmentObjectType, $this->attachmentObjectID, $this->tmpHash, $this->attachmentParentObjectID);
		}
		
		if (empty($_POST)) {
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
		
		if ($this->allowedBBCodesPermission) {
			BBCodeHandler::getInstance()->setAllowedBBCodes(explode(',', WCF::getSession()->getPermission($this->allowedBBCodesPermission)));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'attachmentHandler' => $this->attachmentHandler,
			'attachmentObjectID' => $this->attachmentObjectID,
			'attachmentObjectType' => $this->attachmentObjectType,
			'attachmentParentObjectID' => $this->attachmentParentObjectID,
			'availableContentLanguages' => $this->availableContentLanguages,
			'defaultSmilies' => $this->defaultSmilies,
			'languageID' => ($this->languageID ?: 0),
			'maxTextLength' => $this->maxTextLength,
			'smileyCategories' => $this->smileyCategories,
			'subject' => $this->subject,
			'text' => $this->text,
			'tmpHash' => $this->tmpHash
		]);
		
		if ($this->allowedBBCodesPermission) {
			WCF::getTPL()->assign('allowedBBCodes', explode(',', ArrayUtil::trim(WCF::getSession()->getPermission($this->allowedBBCodesPermission))));
		}
	}
}
