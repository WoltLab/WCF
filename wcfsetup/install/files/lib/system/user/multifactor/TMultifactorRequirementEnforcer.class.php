<?php

namespace wcf\system\user\multifactor;

use wcf\system\exception\NamedUserException;
use wcf\system\WCF;

/**
 * Provides a method enforce the multi-factor requirement.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 * @deprecated 6.1
 */
trait TMultifactorRequirementEnforcer
{
    /**
     * If the current user is in a group that requires multi-factor authentication and
     * they do not have multi-factor authentication enabled, then an exception will be thrown.
     *
     * @throws NamedUserException If the user needs to enable multi-factor authentication.
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
