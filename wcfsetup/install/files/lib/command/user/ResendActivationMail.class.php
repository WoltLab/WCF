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
 * Generates a new code to confirm the email address of a user and sends it to
 * the user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ResendActivationMail
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setEmailConfirmed(Hex::encode(\random_bytes(20)));
        $user = (new UpdateUser($builder))();

        $email = new Email();
        $email->addRecipient(new UserMailbox($user));
        $email->setSubject($user->getLanguage()->getDynamicVariable('wcf.user.register.needActivation.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_registerNeedActivation'),
            new RecipientAwareTextMimePart('text/plain', 'email_registerNeedActivation'),
        ]));
        $email->send();

        return $user;
    }
}
