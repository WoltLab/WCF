<?php

/**
 * Restores the languages `de` and `en` if they are missing.
 *
 * Both languages are authoritative: they define which phrases exist. Installs
 * dating back to before 5.4 could delete them, later versions protect them.
 *
 * A restored language is disabled and not a content language. Its phrases are
 * copied from the default language as placeholders, and only those that a
 * package later ships in a language file for it receive proper values.
 * Phrases written by other PIPs, such as the titles of menu items, keep the
 * placeholder.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - This script must run BEFORE the `*L10n.php` scripts, so that the localized
 *   values of the copied phrases are migrated for the restored languages too.
 * - This script must run BEFORE the `language` PIP, so that the phrases shipped
 *   with the package replace the placeholders of the restored languages.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\language\Language;
use wcf\data\language\LanguageAction;
use wcf\data\language\LanguageEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

$authoritativeLanguages = [
    'de' => [
        'languageName' => 'Deutsch',
        'countryCode' => 'de',
    ],
    'en' => [
        'languageName' => 'English',
        'countryCode' => 'gb',
    ],
];

$sql = "SELECT  *
        FROM    wcf1_language
        WHERE   isDefault = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1]);
$defaultLanguage = $statement->fetchObject(Language::class);

$sql = "SELECT  *
        FROM    wcf1_language
        WHERE   languageCode = ?";
$languageStatement = WCF::getDB()->prepare($sql);

$sql = "SELECT  COUNT(*)
        FROM    wcf1_language_item
        WHERE   languageID = ?";
$countStatement = WCF::getDB()->prepare($sql);

$restoredLanguage = false;
foreach ($authoritativeLanguages as $languageCode => $data) {
    $languageStatement->execute([$languageCode]);
    $language = $languageStatement->fetchObject(Language::class);

    if ($language === null) {
        $language = new LanguageAction([], 'create', [
            'data' => [
                'languageCode' => $languageCode,
                'languageName' => $data['languageName'],
                'countryCode' => $data['countryCode'],
                'isDisabled' => 1,
                'hasContent' => 0,
            ],
        ])->executeAction()['returnValues'];
    }

    // A failure after creating the language must not leave it without phrases
    // on a retry. `copy()` expects an empty language.
    $countStatement->execute([$language->languageID]);
    if ($countStatement->fetchSingleColumn() === 0) {
        new LanguageEditor($defaultLanguage)->copy($language);

        $restoredLanguage = true;
    }
}

if ($restoredLanguage) {
    LanguageFactory::getInstance()->clearCache();
}
