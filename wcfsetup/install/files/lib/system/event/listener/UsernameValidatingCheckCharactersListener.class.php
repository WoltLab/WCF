<?php

namespace wcf\system\event\listener;

use Spoofchecker;
use wcf\event\user\UsernameValidating;

/**
 * Checks the username against the REGISTER_USERNAME_FORCE_ASCII option.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class UsernameValidatingCheckCharactersListener
{
    public function __invoke(UsernameValidating $event): void
    {
        if (!$this->isValid($event->username)) {
            $event->preventDefault();
        }
    }

    private function isValid(string $name): bool
    {
        switch (REGISTER_USERNAME_FORCE_ASCII) {
            case 0:
                break;
            case 1:
                if (!\preg_match('/^[\x20-\x7E]+$/', $name)) {
                    return false;
                }
                break;
            case 2:
                $spoofchecker = new Spoofchecker();
                $checks = Spoofchecker::INVISIBLE;

                // HIDDEN_OVERLAY (256) is available since ICU 62
                if (\version_compare(\INTL_ICU_VERSION, '62.0', '>=')) {
                    if (\defined(Spoofchecker::class . '::HIDDEN_OVERLAY')) {
                        // The constant will exist with PHP 8.3.
                        $checks |= Spoofchecker::HIDDEN_OVERLAY;
                    } else {
                        // HIDDEN_OVERLAY == 256
                        $checks |= 256;
                    }
                }

                // ->setRestrictionLevel() requires ICU 58.
                if (\method_exists($spoofchecker, 'setRestrictionLevel')) {
                    // This method needs to be called first. ->setRestrictionLevel() will
                    // implicitly enable the check for the restriction level for which no
                    // constant exists. When calling ->setChecks() after ->setRestrictionLevel()
                    // the check will be implicitly disabled again.
                    $spoofchecker->setChecks($checks);

                    // https://unicode.org/reports/tr39/#Restriction_Level_Detection
                    $spoofchecker->setRestrictionLevel(Spoofchecker::HIGHLY_RESTRICTIVE);
                } else {
                    $spoofchecker->setChecks($checks | Spoofchecker::SINGLE_SCRIPT);
                }

                \assert(
                    $spoofchecker->isSuspicious("GREEK CAPITAL LETTER SIGMA: \u{03A3}"),
                    "The restriction level check was not correctly enabled."
                );

                if ($spoofchecker->isSuspicious($name)) {
                    return false;
                }
                break;
        }

        return true;
    }
}
