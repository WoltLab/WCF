<?php

namespace wcf\command\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\event\user\UserEnabled;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\event\EventHandler;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;

/**
 * Enable a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableUser
{
    public function __construct(
        private readonly User $user,
        public readonly bool $skipNotification = false,
    ) {}

    public function __invoke(): void
    {
        $this->activateUser($this->user);
        $this->addUserToUserGroup($this->user);
        $this->sendEmailNotification($this->user);

        UserGroupAssignmentHandler::getInstance()->checkUsers([$this->user->userID]);

        $event = new UserEnabled($this->user);
        EventHandler::getInstance()->fire($event);
    }

    private function sendEmailNotification(User $user): void
    {
        if ($this->skipNotification) {
            return;
        }

        $email = new Email();
        $email->setMessageID(
            \sprintf(
                'com.woltlab.wcf.adminActivation/%d/%d/%s',
                $user->userID,
                TIME_NOW,
                \bin2hex(\random_bytes(8))
            )
        );
        $email->addRecipient(new UserMailbox($user));
        $email->setSubject($user->getLanguage()->getDynamicVariable('wcf.acp.user.activation.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_adminActivation'),
            new RecipientAwareTextMimePart('text/plain', 'email_adminActivation'),
        ]));
        $email->send();
    }

    private function addUserToUserGroup(User $user): void
    {
        (new UserAction([$user], 'addToGroups', [
            'groups' => UserGroup::getGroupIDsByType([UserGroup::USERS]),
            'deleteOldGroups' => true,
            'addDefaultGroups' => false,
        ]))->executeAction();
    }

    private function activateUser(User $user): void
    {
        $data = [
            'activationCode' => 0,
            'blacklistMatches' => '',
        ];

        if (!((int)REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER)) {
            $data['emailConfirmed'] = null;
        }

        (new UserEditor($user))->update($data);
    }
}
