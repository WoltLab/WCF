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
use wcf\system\user\command\BanUser;
use wcf\system\WCF;

/**
 * Handles ban a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserBanAction extends UserManagementAction
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

        (new BanUser($userProfile->getDecoratedObject(), $reason, $expires))();
    }

    #[\Override]
    protected function assertUserCanBeManaged(?UserProfile $userProfile): void
    {
        parent::assertUserCanBeManaged($userProfile);

        if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
            throw new PermissionDeniedException();
        }

        if ($userProfile->banned !== 0) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->getDynamicVariable('wcf.user.ban.confirmMessage')
        );
        $form->appendChildren([
            MultilineTextFormField::create('reason')
                ->rows(3)
                ->label('wcf.global.reason')
                ->description('wcf.user.ban.reason.description'),
            BooleanFormField::create('neverExpires')
                ->label('wcf.user.ban.neverExpires')
                ->value(true),
            DateFormField::create('expires')
                ->label('wcf.user.ban.expires')
                ->description('wcf.user.ban.expires.description')
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
