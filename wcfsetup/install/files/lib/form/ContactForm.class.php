<?php

namespace wcf\form;

use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionAction;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\event\page\ContactFormSpamChecking;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\email\Mailbox;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\option\ContactOptionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Customizable contact form with selectable recipients.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ContactForm extends AbstractCaptchaForm
{
    /**
     * @var AttachmentHandler
     */
    public $attachmentHandler;

    /**
     * @var string
     */
    public $attachmentObjectType = 'com.woltlab.wcf.contact';

    /**
     * sender email address
     * @var string
     */
    public $email = '';

    /**
     * sender name
     * @var string
     */
    public $name = '';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_CONTACT_FORM'];

    /**
     * @var ContactOptionHandler
     */
    public $optionHandler;

    /**
     * recipient id
     * @var int
     */
    public $recipientID = 0;

    /**
     * @var ContactRecipientList
     */
    public $recipientList;

    /**
     * @var string
     */
    public $tmpHash = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->optionHandler = new ContactOptionHandler(false);
        $this->optionHandler->init();

        $this->recipientList = new ContactRecipientList();
        $this->recipientList->getConditionBuilder()->add("contact_recipient.isDisabled = ?", [0]);
        $this->recipientList->readObjects();

        if (!\count($this->recipientList)) {
            throw new IllegalLinkException();
        }

        if (isset($_REQUEST['tmpHash'])) {
            $this->tmpHash = $_REQUEST['tmpHash'];
        }
        if (empty($this->tmpHash)) {
            $this->tmpHash = StringUtil::getRandomID();
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->optionHandler->readUserInput($_POST);

        if (isset($_POST['email'])) {
            $this->email = StringUtil::trim($_POST['email']);
        }
        if (isset($_POST['name'])) {
            $this->name = StringUtil::trim($_POST['name']);
        }
        if (isset($_POST['recipientID'])) {
            $this->recipientID = \intval($_POST['recipientID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // validate file options
        $optionHandlerErrors = $this->optionHandler->validate();

        parent::validate();

        if (!empty($optionHandlerErrors)) {
            throw new UserInputException('options', $optionHandlerErrors);
        }

        if (empty($this->email)) {
            throw new UserInputException('email');
        } else {
            try {
                new Mailbox($this->email);
            } catch (\DomainException $e) {
                throw new UserInputException('email', 'invalid');
            }
        }

        if (empty($this->name)) {
            throw new UserInputException('name');
        }

        $recipients = $this->recipientList->getObjects();
        if (\count($recipients) === 1) {
            $this->recipientID = \reset($recipients)->recipientID;
        } else {
            if (!$this->recipientID) {
                throw new UserInputException('recipientID');
            }

            $isValid = false;
            foreach ($recipients as $recipient) {
                if ($this->recipientID == $recipient->recipientID) {
                    $isValid = true;
                    break;
                }
            }

            if (!$isValid) {
                throw new UserInputException('recipientID', 'invalid');
            }
        }

        $this->handleSpamCheck();
    }

    private function handleSpamCheck(): void
    {
        $messages = [];
        foreach ($this->optionHandler->getOptions() as $option) {
            $object = $option['object'];
            \assert($object instanceof ContactOption);
            if (!$object->isMessage || !$object->getOptionValue()) {
                continue;
            }

            $messages[] = $object->getOptionValue();
            if ($object->optionType === 'date' && !$object->getOptionValue()) {
                continue;
            }
        }

        $spamCheckEvent = new ContactFormSpamChecking(
            $this->email,
            UserUtil::getIpAddress(),
            $messages,
        );
        EventHandler::getInstance()->fire($spamCheckEvent);
        if ($spamCheckEvent->defaultPrevented()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        if (CONTACT_FORM_ENABLE_ATTACHMENTS && $this->attachmentObjectType) {
            $this->attachmentHandler = new AttachmentHandler($this->attachmentObjectType, 0, $this->tmpHash, 0);
        }

        parent::readData();

        if (empty($_POST)) {
            if (WCF::getUser()->userID) {
                $this->email = WCF::getUser()->email;
                $this->name = WCF::getUser()->username;
            }

            $this->optionHandler->readData();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $this->objectAction = new ContactOptionAction([], 'send', [
            'attachmentHandler' => $this->attachmentHandler,
            'email' => $this->email,
            'name' => $this->name,
            'optionHandler' => $this->optionHandler,
            'recipientID' => $this->recipientID,
        ]);
        $this->objectAction->executeAction();

        // call saved event
        $this->saved();

        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.contact.success')
        );

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'email' => $this->email,
            'name' => $this->name,
            'options' => $this->optionHandler->getOptions(),
            'recipientList' => $this->recipientList,
            'recipientID' => $this->recipientID,
            'attachmentHandler' => $this->attachmentHandler,
            'attachmentObjectID' => 0,
            'attachmentObjectType' => $this->attachmentObjectType,
            'attachmentParentObjectID' => 0,
            'tmpHash' => $this->tmpHash,
        ]);
    }
}
