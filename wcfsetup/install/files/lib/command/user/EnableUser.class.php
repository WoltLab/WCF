<?php

namespace wcf\command\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;

/**
 * Enables a user that is awaiting activation and moves it from the guests
 * group into the users group.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class EnableUser
{
    public function __construct(
        private readonly User $user,
        private readonly bool $sendNotification = true,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setActivationCode(0)
            ->setBlacklistMatches([])
            ->setGroupIDs($this->getGroupIDs());

        if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) === 0) {
            $builder->setEmailConfirmed(null);
        }

        $user = (new UpdateUser($builder))();

        (new MarkActivationNotificationsAsConfirmed($user))();

        if ($this->sendNotification) {
            $this->sendNotification($user);
        }

        return $user;
    }

    /**
     * @return list<int>
     */
    private function getGroupIDs(): array
    {
        $groupIDs = \array_diff(
            $this->user->getGroupIDs(true),
            UserGroup::getGroupIDsByType([UserGroup::GUESTS])
        );

        return \array_values(\array_unique(\array_merge(
            $groupIDs,
            UserGroup::getGroupIDsByType([UserGroup::USERS])
        )));
    }

    private function sendNotification(User $user): void
    {
        $email = new Email();
        $email->setMessageID(\sprintf(
            'com.woltlab.wcf.adminActivation/%d/%d/%s',
            $user->userID,
            \TIME_NOW,
            \bin2hex(\random_bytes(8))
        ));
        $email->addRecipient(new UserMailbox($user));
        $email->setSubject($user->getLanguage()->getDynamicVariable('wcf.acp.user.activation.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_adminActivation'),
            new RecipientAwareTextMimePart('text/plain', 'email_adminActivation'),
        ]));
        $email->send();
    }
}
