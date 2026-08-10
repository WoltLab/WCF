<?php

namespace wcf\system\worker;

use ParagonIE\ConstantTime\Hex;
use wcf\acp\page\UserListPage;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for sending new passwords.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SendNewPasswordWorker extends AbstractWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 20;

    /**
     * @var array<int, string>
     */
    private array $lostPasswordKeys = [];

    #[\Override]
    public function countObjects()
    {
        $userList = new UserList();
        $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$this->parameters['userIDs']]);

        $this->count = $userList->countObjects();
    }

    #[\Override]
    public function execute()
    {
        $userList = new UserList();
        $userList->decoratorClassName = UserEditor::class;
        $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$this->parameters['userIDs']]);
        $userList->sqlLimit = $this->limit;
        $userList->sqlOffset = $this->limit * $this->loopCount;
        $userList->readObjects();

        /** @var UserEditor $userEditor */
        // @phpstan-ignore varTag.nativeType
        foreach ($userList as $userEditor) {
            $this->resetPassword($userEditor);
        }

        $userList = new UserList();
        $userList->getConditionBuilder()->add('user_table.userID IN (?)', [$this->parameters['userIDs']]);
        $userList->sqlLimit = $this->limit;
        $userList->sqlOffset = $this->limit * $this->loopCount;
        $userList->readObjects();

        /** @var User $user */
        foreach ($userList as $user) {
            $this->sendLink($user);
        }
    }

    #[\Override]
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getControllerLink(UserListPage::class);
    }

    #[\Override]
    public function getProgress()
    {
        $progress = parent::getProgress();

        if ($progress === 100) {
            // unmark users
            ClipboardHandler::getInstance()->unmark(
                $this->parameters['userIDs'],
                ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user')
            );
        }

        return $progress;
    }

    /**
     * Resets the password of the given user.
     *
     * @param UserEditor $userEditor
     * @return void
     */
    protected function resetPassword(UserEditor $userEditor)
    {
        $lostPasswordKey = Hex::encode(\random_bytes(20));
        $lastLostPasswordRequestTime = \TIME_NOW;
        $userAction = new UserAction([$userEditor], 'update', [
            'data' => [
                'password' => null,
                'lostPasswordKey' => \hash('sha256', $lostPasswordKey),
                'lastLostPasswordRequestTime' => $lastLostPasswordRequestTime,
            ],
        ]);
        $userAction->executeAction();

        $this->lostPasswordKeys[$userEditor->userID] = $lostPasswordKey;
    }

    /**
     * Send links.
     *
     * @param User $user
     * @return void
     */
    protected function sendLink(User $user)
    {
        $email = new Email();
        $email->setMessageID(\sprintf(
            'com.woltlab.wcf.sendNewPassword/%d/%d/%s',
            $user->userID,
            \TIME_NOW,
            \bin2hex(\random_bytes(8))
        ));
        $email->addRecipient(new UserMailbox($user));
        $email->setSubject($user->getLanguage()->getDynamicVariable('wcf.acp.user.sendNewPassword.mail.subject'));
        $lostPasswordKey = $this->lostPasswordKeys[$user->userID] ?? '';
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_sendNewPassword', 'wcf', ['lostPasswordKey' => $lostPasswordKey]),
            new RecipientAwareTextMimePart('text/plain', 'email_sendNewPassword', 'wcf', ['lostPasswordKey' => $lostPasswordKey]),
        ]));
        $jobs = $email->getJobs();
        foreach ($jobs as $job) {
            BackgroundQueueHandler::getInstance()->performJob($job);
        }
    }

    #[\Override]
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.user.canEditPassword']);

        if (
            !isset($this->parameters['userIDs'])
            || !\is_array($this->parameters['userIDs'])
            || $this->parameters['userIDs'] === []
        ) {
            throw new SystemException("'userIDs' parameter is missing or invalid");
        }
    }
}
