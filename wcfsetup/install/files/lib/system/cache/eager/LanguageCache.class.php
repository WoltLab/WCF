<?php

namespace wcf\system\cache\eager;

use wcf\data\DatabaseObject;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\LanguageList;

/**
 * Eager cache implementation for languages. It consists of plain arrays only,
 * which allows the OPcache to keep it in shared memory, the `Language` and
 * `LanguageCategory` objects are created on demand by `LanguageFactory`.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @phpstan-type LanguageRow array{
 *     languageID: int,
 *     languageCode: string,
 *     languageName: string,
 *     countryCode: string,
 *     isDefault: int,
 *     hasContent: int,
 *     isDisabled: int,
 *     locale: string,
 * }
 * @phpstan-type LanguageCategoryRow array{
 *     languageCategoryID: int,
 *     languageCategory: string,
 * }
 *
 * @extends AbstractEagerCache<array{
 *     languages: array<int, LanguageRow>,
 *     codes: array<string, int>,
 *     default: int,
 *     categories: array<string, LanguageCategoryRow>,
 *     categoryNames: array<int, string>,
 *     multilingualismEnabled: bool,
 * }>
 */
final class LanguageCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $languageList = new LanguageList();
        $languageList->getConditionBuilder()->add('language.isDisabled = ?', [0]);
        $languageList->readObjects();

        $languages = $languageList->getObjects();
        $default = 0;
        $multilingualismEnabled = false;
        $codes = [];

        foreach ($languages as $language) {
            if ($language->isDefault !== 0) {
                $default = $language->languageID;
            }

            if ($language->hasContent !== 0) {
                $multilingualismEnabled = true;
            }

            $codes[$language->languageCode] = $language->languageID;
        }

        DatabaseObject::sort($languages, 'languageName');

        $languageCategoryList = new LanguageCategoryList();
        $languageCategoryList->readObjects();

        $categories = [];
        $categoryNames = [];
        foreach ($languageCategoryList->getObjects() as $languageCategory) {
            $categories[$languageCategory->languageCategory] = $languageCategory->getData();
            $categoryNames[$languageCategory->languageCategoryID] = $languageCategory->languageCategory;
        }

        if (!isset($languages[$default])) {
            throw new \RuntimeException('No default language defined!');
        }

        return [
            'languages' => \array_map(static fn($language) => $language->getData(), $languages),
            'codes' => $codes,
            'default' => $default,
            'categories' => $categories,
            'categoryNames' => $categoryNames,
            'multilingualismEnabled' => $multilingualismEnabled,
        ];
    }
}
