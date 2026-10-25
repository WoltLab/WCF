<?php

namespace wcf\system\cache\builder;

use wcf\data\language\category\LanguageCategory;
use wcf\data\language\Language;
use wcf\system\cache\eager\LanguageCache;

/**
 * Caches languages and the id of the default language.
 *
 * @author Olaf Braun, Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type LanguageCacheData array{
 *  codes: array<string, int>,
 *  countryCodes: array<int, string>,
 *  languages: array<int, Language>,
 *  default: int,
 *  categories: array<string, LanguageCategory>,
 *  categoryIDs: array<int, string>,
 *  multilingualismEnabled: bool,
 * }
 *
 * @deprecated 6.2 use `LanguageCache` instead
 */
class LanguageCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function reset(array $parameters = [])
    {
        new LanguageCache()->rebuild();
    }

    #[\Override]
    public function rebuild(array $parameters): array
    {
        $cacheData = new LanguageCache()->getCache();

        return [
            'codes' => $cacheData->codes,
            'countryCodes' => $cacheData->countryCodes,
            'languages' => $cacheData->languages,
            'default' => $cacheData->default,
            'categories' => $cacheData->categories,
            'categoryIDs' => $cacheData->categoryIDs,
            'multilingualismEnabled' => $cacheData->multilingualismEnabled,
        ];
    }
}
