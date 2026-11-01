<?php

namespace wcf\system\language;

use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
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
 *
 * @phpstan-import-type LanguageRow from LanguageCache
 * @phpstan-import-type LanguageCategoryRow from LanguageCache
 */
class LanguageFactory extends SingletonFactory
{
    /**
     * database rows of the enabled languages, sorted by name
     * @var array<int, LanguageRow>
     */
    private array $languageRows = [];

    /**
     * language ids by language code
     * @var array<string, int>
     */
    private array $languageIDsByCode = [];

    private int $defaultLanguageID = 0;

    /**
     * database rows of the language categories by name
     * @var array<string, LanguageCategoryRow>
     */
    private array $categoryRows = [];

    /**
     * language category names by id
     * @var array<int, string>
     */
    private array $categoryNames = [];

    private bool $multilingualismEnabled = false;

    /**
     * languages created so far
     * @var array<int, Language>
     */
    private array $languages = [];

    /**
     * language categories created so far
     * @var array<string, LanguageCategory>
     */
    private array $categories = [];

    /**
     * active template scripting compiler
     * @var ?TemplateScriptingCompiler
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
        if (!isset($this->languages[$languageID])) {
            if (!isset($this->languageRows[$languageID])) {
                return null;
            }

            $this->languages[$languageID] = new Language(null, $this->languageRows[$languageID]);
        }

        return $this->languages[$languageID];
    }

    /**
     * Returns the preferred language of the current user.
     */
    public function getUserLanguage(?int $languageID = null): Language
    {
        if ($languageID !== null) {
            $language = $this->getLanguage($languageID);
            if ($language !== null) {
                return $language;
            }
        }

        $languageID = $this->findPreferredLanguage();

        return $this->getLanguage($languageID);
    }

    /**
     * Returns the language with the given language code or null if no such
     * language exists.
     */
    public function getLanguageByCode(string $languageCode): ?Language
    {
        $languageID = $this->languageIDsByCode[$languageCode] ?? null;
        if ($languageID === null) {
            return null;
        }

        return $this->getLanguage($languageID);
    }

    /**
     * Returns true if the language category with the given name exists.
     */
    public function isValidCategory(string $categoryName): bool
    {
        return isset($this->categoryRows[$categoryName]);
    }

    /**
     * Returns the language category with the given name.
     */
    public function getCategory(string $categoryName): ?LanguageCategory
    {
        if (!isset($this->categories[$categoryName])) {
            if (!isset($this->categoryRows[$categoryName])) {
                return null;
            }

            $this->categories[$categoryName] = new LanguageCategory(null, $this->categoryRows[$categoryName]);
        }

        return $this->categories[$categoryName];
    }

    /**
     * Returns language category by id.
     */
    public function getCategoryByID(int $languageCategoryID): ?LanguageCategory
    {
        $categoryName = $this->categoryNames[$languageCategoryID] ?? null;
        if ($categoryName === null) {
            return null;
        }

        return $this->getCategory($categoryName);
    }

    /**
     * Returns a list of available language categories.
     *
     * @return LanguageCategory[]
     */
    public function getCategories(): array
    {
        $categories = [];
        foreach (\array_keys($this->categoryRows) as $categoryName) {
            $categories[$categoryName] = $this->getCategory($categoryName);
        }

        return $categories;
    }

    /**
     * Searches the preferred language of the current user.
     *
     * @return int
     */
    protected function findPreferredLanguage(): int
    {
        $defaultLanguageCode = $this->languageRows[$this->defaultLanguageID]['languageCode'];

        // get preferred language
        $languageCode = self::getPreferredLanguage(\array_keys($this->languageIDsByCode), $defaultLanguageCode);

        // get language id of preferred language
        foreach ($this->languageRows as $key => $languageRow) {
            if ($languageRow['languageCode'] === $languageCode) {
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
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $negotiator = new LanguageNegotiator();

            $preferredLanguage = $negotiator->getBest(
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                \array_map(
                    static fn($availableLanguageCode) => \strtolower(self::fixLanguageCode($availableLanguageCode)),
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
        $data = new LanguageCache()->getCache();

        $this->languageRows = $data['languages'];
        $this->languageIDsByCode = $data['codes'];
        $this->defaultLanguageID = $data['default'];
        $this->categoryRows = $data['categories'];
        $this->categoryNames = $data['categoryNames'];
        $this->multilingualismEnabled = $data['multilingualismEnabled'];
    }

    /**
     * Clears languages cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        new LanguageCache()->rebuild();
    }

    /**
     * Removes additional language identifier from given language code.
     * Converts e.g. 'de-informal' to 'de'.
     *
     * @return string $languageCode
     */
    public static function fixLanguageCode(string $languageCode)
    {
        return \preg_replace('/-[a-z0-9]+/', '', $languageCode);
    }

    /**
     * Returns the default language object.
     */
    public function getDefaultLanguage(): Language
    {
        return $this->getLanguage($this->defaultLanguageID);
    }

    /**
     * Returns the default language id
     */
    public function getDefaultLanguageID(): int
    {
        return $this->defaultLanguageID;
    }

    /**
     * Returns all available languages.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        $languages = [];
        foreach (\array_keys($this->languageRows) as $languageID) {
            $languages[$languageID] = $this->getLanguage($languageID);
        }

        return $languages;
    }

    /**
     * Returns all available content languages for given package.
     *
     * @return array<int, Language>
     */
    public function getContentLanguages(): array
    {
        return \array_filter(
            $this->getLanguages(),
            static fn(Language $language) => \boolval($language->hasContent)
        );
    }

    /**
     * Returns the list of content language ids.
     *
     * @return int[]
     */
    public function getContentLanguageIDs(): array
    {
        return \array_keys($this->getContentLanguages());
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

        foreach ($this->getLanguages() as $language) {
            $languageEditor = new LanguageEditor($language);
            $languageEditor->deleteCompiledTemplates();
        }
    }

    /**
     * Returns true if multilingualism is enabled.
     */
    public function multilingualismEnabled(): bool
    {
        return $this->multilingualismEnabled;
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
        $statement->execute([\TIME_NOW - 86400 * 7]);

        return $statement->fetchSingleColumn();
    }
}
