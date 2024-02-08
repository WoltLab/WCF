<?php

namespace wcf\form;

use wcf\data\language\Language;
use wcf\data\smiley\category\SmileyCategory;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\censorship\Censorship;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * MessageForm is an abstract form implementation for a message with optional captcha support.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class MessageForm extends AbstractCaptchaForm
{
    /**
     * attachment handler
     * @var AttachmentHandler
     */
    public $attachmentHandler;

    /**
     * object id for attachments
     * @var int
     */
    public $attachmentObjectID = 0;

    /**
     * object type for attachments, if left blank, attachment support is disabled
     * @var string
     */
    public $attachmentObjectType = '';

    /**
     * parent object id for attachments
     * @var int
     */
    public $attachmentParentObjectID = 0;

    /**
     * list of available content languages
     * @var Language[]
     */
    public $availableContentLanguages = [];

    /**
     * list of default smilies
     * @var Smiley[]
     */
    public $defaultSmilies = [];

    /**
     * name of the permission which contains the disallowed BBCodes
     * @var string
     */
    public $disallowedBBCodesPermission = 'user.message.disallowedBBCodes';

    /**
     * enables multilingualism
     * @var bool
     */
    public $enableMultilingualism = false;

    /**
     * @var HtmlInputProcessor
     */
    public $htmlInputProcessor;

    /**
     * content language id
     * @var int
     */
    public $languageID;

    /**
     * maximum text length
     * @var int
     */
    public $maxTextLength = 0;

    /**
     * message object type for html processing
     * @var string
     */
    public $messageObjectType = '';

    /**
     * list of smiley categories
     * @var SmileyCategory[]
     */
    public $smileyCategories = [];

    /**
     * message subject
     * @var string
     */
    public $subject = '';

    /**
     * message text
     * @var string
     */
    public $text = '';

    /**
     * temp hash
     * @var string
     */
    public $tmpHash = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['tmpHash'])) {
            $this->tmpHash = $_REQUEST['tmpHash'];
        }
        if (empty($this->tmpHash)) {
            /** @deprecated 5.5 see QuickReplyManager::setTmpHash() */
            $this->tmpHash = WCF::getSession()->getVar('__wcfAttachmentTmpHash');
            if ($this->tmpHash === null) {
                $this->tmpHash = StringUtil::getRandomID();
            } else {
                WCF::getSession()->unregister('__wcfAttachmentTmpHash');
            }
        }

        if ($this->enableMultilingualism) {
            $this->availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
            if (WCF::getUser()->userID) {
                foreach ($this->availableContentLanguages as $key => $value) {
                    if (!\in_array($key, WCF::getUser()->getLanguageIDs())) {
                        unset($this->availableContentLanguages[$key]);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['subject'])) {
            $this->subject = StringUtil::trim(MessageUtil::stripCrap($_POST['subject']));
        }
        if (isset($_POST['text'])) {
            $this->text = StringUtil::trim(MessageUtil::stripCrap($_POST['text']));
        }

        // multilingualism
        if (isset($_POST['languageID'])) {
            $this->languageID = \intval($_POST['languageID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
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
    protected function validateSubject()
    {
        if (empty($this->subject)) {
            throw new UserInputException('subject');
        }

        if (\mb_strlen($this->subject) > 255) {
            $this->subject = \mb_substr($this->subject, 0, 255);
        }

        $censoredWords = Censorship::getInstance()->test($this->subject);
        if ($censoredWords) {
            WCF::getTPL()->assign('censoredWords', $censoredWords);
            throw new UserInputException('subject', 'censoredWordsFound');
        }
    }

    /**
     * Validates the message text.
     */
    protected function validateText()
    {
        if (empty($this->messageObjectType)) {
            throw new \RuntimeException("Expected non-empty message object type for '" . static::class . "'");
        }

        if (empty($this->text)) {
            throw new UserInputException('text');
        }

        if ($this->disallowedBBCodesPermission) {
            BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                ',',
                WCF::getSession()->getPermission($this->disallowedBBCodesPermission)
            ));
        }

        $this->htmlInputProcessor = new HtmlInputProcessor();
        $this->htmlInputProcessor->process($this->text, $this->messageObjectType, 0);

        // check text length
        if ($this->htmlInputProcessor->appearsToBeEmpty()) {
            throw new UserInputException('text');
        }
        $message = $this->htmlInputProcessor->getTextContent();
        if ($this->maxTextLength != 0 && \mb_strlen($message) > $this->maxTextLength) {
            throw new UserInputException('text', 'tooLong');
        }

        $disallowedBBCodes = $this->htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
            throw new UserInputException('text', 'disallowedBBCodes');
        }

        // search for censored words
        $censoredWords = Censorship::getInstance()->test($message);
        if ($censoredWords) {
            WCF::getTPL()->assign('censoredWords', $censoredWords);
            throw new UserInputException('text', 'censoredWordsFound');
        }
    }

    /**
     * Validates content language id.
     */
    protected function validateContentLanguage()
    {
        if (!$this->languageID || !$this->enableMultilingualism || empty($this->availableContentLanguages)) {
            $this->languageID = null;

            return;
        }

        if (!isset($this->availableContentLanguages[$this->languageID])) {
            throw new UserInputException('languageID', 'invalid');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $this->text = $this->htmlInputProcessor->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // get attachments
        if ($this->attachmentObjectType) {
            $this->attachmentHandler = new AttachmentHandler(
                $this->attachmentObjectType,
                $this->attachmentObjectID,
                $this->tmpHash,
                $this->attachmentParentObjectID
            );
        }

        if (empty($_POST)) {
            $this->languageID = WCF::getLanguage()->languageID;
        }

        parent::readData();

        // get default smilies
        if (MODULE_SMILEY) {
            $this->smileyCategories = SmileyCache::getInstance()->getVisibleCategories();

            $firstCategory = \reset($this->smileyCategories);
            if ($firstCategory) {
                $this->defaultSmilies = SmileyCache::getInstance()
                    ->getCategorySmilies($firstCategory->categoryID ?: null);
            }
        }

        if ($this->disallowedBBCodesPermission) {
            BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                ',',
                WCF::getSession()->getPermission($this->disallowedBBCodesPermission)
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($this->text, $this->messageObjectType, 0);

        WCF::getTPL()->assign([
            'attachmentHandler' => $this->attachmentHandler,
            'attachmentObjectID' => $this->attachmentObjectID,
            'attachmentObjectType' => $this->attachmentObjectType,
            'attachmentParentObjectID' => $this->attachmentParentObjectID,
            'availableContentLanguages' => $this->availableContentLanguages,
            'defaultSmilies' => $this->defaultSmilies,
            'languageID' => $this->languageID ?: 0,
            'maxTextLength' => $this->maxTextLength,
            'smileyCategories' => $this->smileyCategories,
            'subject' => $this->subject,
            'text' => $upcastProcessor->getHtml(),
            'tmpHash' => $this->tmpHash,
        ]);
    }
}
