<?php

namespace wcf\system\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Decides which administrative actions the active user may perform on a user.
 * Shared by the interactions, the RPC endpoints and the dialogs of the user
 * administration to keep the visible actions and the server-side checks in sync.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserAdministrationPolicy
{
    /**
     * Returns true if all groups of the user are accessible to the active user
     * and, unless `$allowSelf` is set, the user is not the active user.
     */
    public static function isManageable(User $user, bool $allowSelf = false): bool
    {
        if (!$allowSelf && $user->userID === WCF::getUser()->userID) {
            return false;
        }

        return UserGroup::isAccessibleGroup($user->getGroupIDs());
    }

    /**
     * Returns true if the user can be enabled or disabled and if the email
     * address of the user can be confirmed or unconfirmed.
     */
    public static function canEnable(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canEnableUser')
            && self::isManageable($user);
    }

    public static function canBan(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canBanUser')
            && self::isManageable($user);
    }

    /**
     * Returns true if a link to set a new password can be sent to the user,
     * which is not possible for users that log in via a third party provider.
     */
    public static function canSendNewPassword(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canEditPassword')
            && $user->authData === ''
            && self::isManageable($user);
    }

    public static function canResendActivationMail(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canEnableUser')
            && $user->canEmailConfirm()
            && self::isManageable($user);
    }

    /**
     * Returns true if the user and its content can be deleted.
     */
    public static function canDelete(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canDeleteUser')
            && self::isManageable($user);
    }

    public static function canExportGdpr(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canExportGdprData')
            && self::isManageable($user, true);
    }

    public static function canMail(User $user): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canMailUser')
            && $user->userID !== WCF::getUser()->userID;
    }

    public static function canEdit(User $user): bool
    {
        return $user->canEdit();
    }

    /**
     * @throws PermissionDeniedException
     */
    public static function assertCanEnable(User $user): void
    {
        if (!self::canEnable($user)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @throws PermissionDeniedException
     */
    public static function assertCanBan(User $user): void
    {
        if (!self::canBan($user)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @throws PermissionDeniedException
     */
    public static function assertCanSendNewPassword(User $user): void
    {
        if (!self::canSendNewPassword($user)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @throws PermissionDeniedException
     */
    public static function assertCanResendActivationMail(User $user): void
    {
        if (!self::canResendActivationMail($user)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @throws PermissionDeniedException
     */
    public static function assertCanDelete(User $user): void
    {
        if (!self::canDelete($user)) {
            throw new PermissionDeniedException();
        }
    }
}
