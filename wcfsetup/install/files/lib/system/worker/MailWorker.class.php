<?php

namespace wcf\system\worker;

use wcf\data\user\User;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for sending mails.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MailWorker extends AbstractWorker
{
    /**
     * condition builder object
     * @var PreparedStatementConditionBuilder
     */
    protected $conditions;

    /**
     * @inheritDoc
     */
    protected $limit = 50;

    /**
     * mail data
     * @var array
     */
    protected $mailData;

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.user.canMailUser']);

        if (!isset($this->parameters['mailID'])) {
            throw new SystemException("mailID missing");
        }

        $userMailData = WCF::getSession()->getVar('userMailData');
        if (!isset($userMailData[$this->parameters['mailID']])) {
            throw new SystemException("mailID '" . $this->parameters['mailID'] . "' is invalid");
        }

        $this->mailData = $userMailData[$this->parameters['mailID']];
        if (!isset($this->mailData['message-id'])) {
            $this->mailData['message-id'] = \sprintf(
                'com.woltlab.wcf.mailWorker/%d/%s',
                TIME_NOW,
                \bin2hex(\random_bytes(8))
            );
            $userMailData[$this->parameters['mailID']] = $this->mailData;
            WCF::getSession()->register('userMailData', $userMailData);
        }
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $this->conditions = new PreparedStatementConditionBuilder();
        if ($this->mailData['action'] == '') {
            $this->conditions->add("user.userID IN (?)", [$this->mailData['userIDs']]);
        } else {
            $this->conditions->add("user.emailConfirmed IS NULL");
            $this->conditions->add("user.banned = ?", [0]);

            if ($this->mailData['action'] == 'group') {
                $this->conditions->add(
                    "user.userID IN (
                        SELECT  userID
                        FROM    wcf1_user_to_group
                        WHERE   groupID IN (?)
                    )",
                    [$this->mailData['groupIDs']]
                );
            }
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user user
                " . $this->conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->conditions->getParameters());

        $this->count = $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function getProgress()
    {
        $progress = parent::getProgress();

        if ($progress == 100) {
            // clear markings
            $typeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
            ClipboardHandler::getInstance()->removeItems($typeID);

            // clear session
            $userMailData = WCF::getSession()->getVar('userMailData');
            unset($userMailData[$this->parameters['mailID']]);
            WCF::getSession()->register('userMailData', $userMailData);
        }

        return $progress;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $email = new Email();
        $email->setMessageID($this->mailData['message-id']);
        $email->setSubject($this->mailData['subject']);
        $from = new Mailbox(
            $this->mailData['from'],
            (!empty($this->mailData['fromName']) ? $this->mailData['fromName'] : null)
        );
        $email->setSender($from);
        $email->setReplyTo($from);
        $variables = [
            'text' => $this->mailData['text'],
            'enableHTML' => $this->mailData['enableHTML'] ? true : false,
        ];
        if ($this->mailData['enableHTML']) {
            $email->setBody(new RecipientAwareTextMimePart('text/html', 'email_mailWorker', 'wcf', $variables));
        } else {
            $email->setBody(new MimePartFacade([
                new RecipientAwareTextMimePart('text/html', 'email_mailWorker', 'wcf', $variables),
                new RecipientAwareTextMimePart('text/plain', 'email_mailWorker', 'wcf', $variables),
            ]));
        }

        // get users
        $sql = "SELECT      user_option.*, user.*
                FROM        wcf1_user user
                LEFT JOIN   wcf1_user_option_value user_option
                ON          user_option.userID = user.userID
                " . $this->conditions . "
                ORDER BY    user.userID";
        $statement = WCF::getDB()->prepare($sql, $this->limit, $this->limit * $this->loopCount);
        $statement->execute($this->conditions->getParameters());
        while ($row = $statement->fetchArray()) {
            $user = new User(null, $row);
            $adminCanMail = $user->adminCanMail;
            if ($adminCanMail === null || $adminCanMail) {
                $this->sendMail($email, $user);
            }
        }
    }

    /**
     * Sends the given blueprint (Email without recipients) to the given user.
     *
     * @param Email $blueprint
     * @param User $user
     */
    protected function sendMail(Email $blueprint, User $user)
    {
        $email = clone $blueprint;
        $email->addRecipient(new UserMailbox($user));
        $jobs = $email->getJobs();
        foreach ($jobs as $job) {
            BackgroundQueueHandler::getInstance()->performJob($job);
        }
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('UserList');
    }
}
