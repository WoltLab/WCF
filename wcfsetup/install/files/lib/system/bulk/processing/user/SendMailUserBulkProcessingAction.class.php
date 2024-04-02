<?php

namespace wcf\system\bulk\processing\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk processing action implementation for sending mails to users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class SendMailUserBulkProcessingAction extends AbstractUserBulkProcessingAction
{
    /**
     * is 1 if HTML for the email is enabled
     * @var int
     */
    public $enableHTML = 0;

    /**
     * identifier for the mail worker
     * @var string
     */
    public $mailID = '';

    /**
     * email subject
     * @var string
     */
    public $subject = '';

    /**
     * email text
     * @var string
     */
    public $text = '';

    /**
     * @inheritDoc
     */
    public function executeAction(DatabaseObjectList $objectList)
    {
        if (!($objectList instanceof UserList)) {
            throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
        }

        if (\count($objectList)) {
            // save config in session
            $userMailData = WCF::getSession()->getVar('userMailData');
            if ($userMailData === null) {
                $userMailData = [];
            }
            $this->mailID = \count($userMailData) + 1;
            $userMailData[$this->mailID] = [
                'action' => '',
                'enableHTML' => $this->enableHTML,
                'from' => \MAIL_FROM_ADDRESS,
                'fromName' => \MAIL_FROM_NAME,
                'groupIDs' => '',
                'subject' => $this->subject,
                'text' => $this->text,
                'userIDs' => $objectList->getObjectIDs(),
            ];
            WCF::getSession()->register('userMailData', $userMailData);
        }
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return WCF::getTPL()->fetch('sendMailUserBulkProcessing', 'wcf', [
            'enableHTML' => $this->enableHTML,
            'mailID' => $this->mailID,
            'subject' => $this->subject,
            'text' => $this->text,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['enableHTML'])) {
            $this->enableHTML = \intval($_POST['enableHTML']);
        }
        if (isset($_POST['subject'])) {
            $this->subject = StringUtil::trim($_POST['subject']);
        }
        if (isset($_POST['text'])) {
            $this->text = StringUtil::trim($_POST['text']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if (empty($this->subject)) {
            throw new UserInputException('subject');
        }

        if (empty($this->text)) {
            throw new UserInputException('text');
        }
    }
}
