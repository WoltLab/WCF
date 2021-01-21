<?php

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
