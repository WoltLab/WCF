<?php

namespace wcf\system\language;

use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\system\cache\eager\data\LanguageCacheData;
use wcf\system\cache\eager\LanguageCache;
use wcf\system\SingletonFactory;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;

/**
 * Handles language related functions.
 *
 * @author  Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageFactory extends SingletonFactory
{
    /**
     * language cache
     */
    protected LanguageCacheData $cache;

    /**
     * active template scripting compiler
     * @var TemplateScriptingCompiler
     */
    protected $scriptingCompiler;

    #[\Override]
    protected function init()
    {
        $this->loadCache();
    }

    /**
     * Returns a Language object for the language with the given id.
     */
    public function getLanguage(int $languageID): ?Language
    {
        return $this->cache->getLanguage($languageID);
    }

    /**
     * Returns the preferred language of the current user.
     */
    public function getUserLanguage(?int $languageID = null): Language
    {
        if ($languageID) {
            $language = $this->cache->getLanguage($languageID);
            if ($language !== null) {
                return $language;
            }
        }

        $languageID = $this->findPreferredLanguage();

        return $this->cache->getLanguage($languageID);
    }

    /**
     * Returns the language with the given language code or null if no such
     * language exists.
     */
    public function getLanguageByCode(string $languageCode): ?Language
    {
        return $this->cache->getLanguageByCode($languageCode);
    }

    /**
     * Returns true if the language category with the given name exists.
     */
    public function isValidCategory(string $categoryName): bool
    {
        return $this->cache->hasCategory($categoryName);
    }

    /**
     * Returns the language category with the given name.
     */
    public function getCategory(string $categoryName): ?LanguageCategory
    {
        return $this->cache->getCategory($categoryName);
    }

    /**
     * Returns language category by id.
     */
    public function getCategoryByID(int $languageCategoryID): ?LanguageCategory
    {
        return $this->cache->getCategoryByID($languageCategoryID);
    }

    /**
     * Returns a list of available language categories.
     *
     * @return LanguageCategory[]
     */
    public function getCategories(): array
    {
        return $this->cache->categories;
    }

    /**
     * Searches the preferred language of the current user.
     *
     * @return int
     */
    protected function findPreferredLanguage(): int
    {
        $defaultLanguageCode = $this->cache->getDefaultLanguage()->languageCode;

        // get preferred language
        $languageCode = self::getPreferredLanguage($this->cache->getLanguageCodes(), $defaultLanguageCode);

        // get language id of preferred language
        foreach ($this->cache->languages as $key => $language) {
            if ($language->languageCode === $languageCode) {
                return $key;
            }
        }

        return 0;
    }

    /**
     * Determines the preferred language of the current user.
     *
     * @param string[] $availableLanguageCodes
     * @return string
     */
    public static function getPreferredLanguage(array $availableLanguageCodes, string $defaultLanguageCode): string
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
            $negotiator = new LanguageNegotiator();

            $preferredLanguage = $negotiator->getBest(
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                \array_map(
                    static fn ($availableLanguageCode) => \strtolower(self::fixLanguageCode($availableLanguageCode)),
                    $availableLanguageCodes
                )
            );

            if ($preferredLanguage !== null) {
                \assert($preferredLanguage instanceof AcceptLanguage);

                return $preferredLanguage->getValue();
            }
        }

        return $defaultLanguageCode;
    }

    /**
     * Returns the active scripting compiler object.
     */
    public function getScriptingCompiler(): TemplateScriptingCompiler
    {
        if ($this->scriptingCompiler === null) {
            $this->scriptingCompiler = new TemplateScriptingCompiler(WCF::getTPL());
        }

        return $this->scriptingCompiler;
    }

    /**
     * Loads the language cache.
     *
     * @return void
     */
    protected function loadCache(): void
    {
        $this->cache = (new LanguageCache())->getCache();
    }

    /**
     * Clears languages cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        (new LanguageCache())->rebuild();
    }

    /**
     * Removes additional language identifier from given language code.
     * Converts e.g. 'de-informal' to 'de'.
     *
     * @param string $languageCode
     * @return string $languageCode
     */
    public static function fixLanguageCode($languageCode)
    {
        return \preg_replace('/-[a-z0-9]+/', '', $languageCode);
    }

    /**
     * Returns the default language object.
     * @since 3.0
     */
    public function getDefaultLanguage(): Language
    {
        return $this->cache->getDefaultLanguage();
    }

    /**
     * Returns the default language id
     */
    public function getDefaultLanguageID(): int
    {
        return $this->cache->default;
    }

    /**
     * Returns all available languages.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        return $this->cache->languages;
    }

    /**
     * Returns all available content languages for given package.
     *
     * @return array<int, Language>
     */
    public function getContentLanguages(): array
    {
        return $this->cache->getContentLanguages();
    }

    /**
     * Returns the list of content language ids.
     *
     * @return int[]
     * @since 3.1
     */
    public function getContentLanguageIDs(): array
    {
        return $this->cache->getContentLanguageIDs();
    }

    /**
     * Makes given language the default language.
     */
    public function makeDefault(int $languageID): void
    {
        // remove old default language
        $sql = "UPDATE  wcf1_language
                SET     isDefault = 0
                WHERE   isDefault = 1";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        // make this language to default
        $sql = "UPDATE  wcf1_language
                SET     isDefault = 1,
                        isDisabled = 0
                WHERE   languageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$languageID]);

        // rebuild language cache
        $this->clearCache();
    }

    /**
     * Removes language cache and compiled templates.
     *
     * @return void
     */
    public function deleteLanguageCache(): void
    {
        LanguageEditor::deleteLanguageFiles();

        foreach ($this->cache->languages as $language) {
            $languageEditor = new LanguageEditor($language);
            $languageEditor->deleteCompiledTemplates();
        }
    }

    /**
     * Returns true if multilingualism is enabled.
     */
    public function multilingualismEnabled(): bool
    {
        return $this->cache->multilingualismEnabled;
    }

    /**
     * Returns the number of phrases that have been automatically disabled in the past 7 days.
     */
    public function countRecentlyDisabledCustomValues(): int
    {
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf1_language_item
                WHERE   languageCustomItemDisableTime >= ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([TIME_NOW - 86400 * 7]);

        return $statement->fetchSingleColumn();
    }
}
