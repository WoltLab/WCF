<?php

/**
 * Checks that an owner group has been selected.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\user\group\UserGroup;
use wcf\system\WCF;

if (UserGroup::getOwnerGroupID() === null) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Es wurde noch keine Besitzer-Gruppe festgelegt.";
    } else {
        $message = "No owner group was specified yet.";
    }

    throw new \RuntimeException($message);
}
