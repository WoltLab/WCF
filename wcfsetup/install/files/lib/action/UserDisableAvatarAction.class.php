<?php

namespace wcf\action;

use wcf\data\user\UserProfile;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\user\command\DisableAvatar;
use wcf\system\WCF;

/**
 * Handles disabling of user avatars.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserDisableAvatarAction extends UserManagementAction
{
    #[\Override]
    protected function performAction(UserProfile $userProfile, array $data): void
    {
        $reason = $data['reason'];
        if ($data['neverExpires']) {
            $expires = null;
        } else {
            $expires = $data['expires'];
        }

        (new DisableAvatar($userProfile->getDecoratedObject(), $reason, $expires))();
    }

    #[\Override]
    protected function assertUserCanBeManaged(UserProfile $userProfile): void
    {
        parent::assertUserCanBeManaged($userProfile);

        if (!WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
            throw new PermissionDeniedException();
        }

        if ($userProfile->disableAvatar !== 0) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->getDynamicVariable('wcf.user.disableAvatar.confirmMessage')
        );
        $form->appendChildren([
            MultilineTextFormField::create('reason')
                ->rows(3)
                ->label('wcf.global.reason'),
            BooleanFormField::create('neverExpires')
                ->label('wcf.user.disableAvatar.neverExpires')
                ->value(true),
            DateFormField::create('expires')
                ->label('wcf.user.disableAvatar.expires')
                ->description('wcf.user.disableAvatar.expires.description')
                ->earliestDate(TIME_NOW)
                ->required()
                ->addDependency(
                    EmptyFormFieldDependency::create('neverExpires')
                        ->fieldId('neverExpires')
                ),
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
