<?php

namespace wcf\system\user\multifactor;

use wcf\system\exception\NamedUserException;
use wcf\system\WCF;

/**
 * Provides a method enforce the multifactor requirement.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication
 * @since   5.4
 */
trait TMultifactorRequirementEnforcer
{
    /**
     * If the current user is in a group that requires multifactor authentication, and
     * they do not have multifactor authentication enabled, then an exception will be thrown.
     *
     * @throws NamedUserException If the user needs to enable multifactor authentication.
     */
    private function enforceMultifactorAuthentication(): void
    {
        if (
            WCF::getUser()->requiresMultifactor()
            && !WCF::getUser()->multifactorActive
        ) {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.security.requiresMultifactor'
            ));
        }
    }
}
