<?php

namespace wcf\command\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\system\email\Email;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;

/**
 * Invalidates the password of a user and sends a link to set a new password.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SendNewPassword
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        $lostPasswordKey = Hex::encode(\random_bytes(20));

        $builder = UserBuilder::forUpdate($this->user)
            ->setPassword(null)
            ->setLostPasswordKey(\hash('sha256', $lostPasswordKey))
            ->setLastLostPasswordRequestTime(\TIME_NOW);
        $user = new UpdateUser($builder)();

        $email = new Email();
        $email->setMessageID(\sprintf(
            'com.woltlab.wcf.sendNewPassword/%d/%d/%s',
            $user->userID,
            \TIME_NOW,
            \bin2hex(\random_bytes(8))
        ));
        $email->addRecipient(new UserMailbox($user));
        $email->setSubject($user->getLanguage()->getDynamicVariable('wcf.acp.user.sendNewPassword.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_sendNewPassword', 'wcf', ['lostPasswordKey' => $lostPasswordKey]),
            new RecipientAwareTextMimePart('text/plain', 'email_sendNewPassword', 'wcf', ['lostPasswordKey' => $lostPasswordKey]),
        ]));
        $email->send();
    }
}
