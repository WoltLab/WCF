<?php

namespace wcf\system\language;

use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\system\cache\builder\LanguageCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;

/**
 * Handles language related functions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageFactory extends SingletonFactory
{
    /**
     * language cache
     * @var mixed[]
     */
    protected $cache;

    /**
     * initialized languages
     * @var Language[]
     */
    protected $languages = [];

    /**
     * active template scripting compiler
     * @var TemplateScriptingCompiler
     */
    protected $scriptingCompiler;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->loadCache();
    }

    /**
     * Returns a Language object for the language with the given id.
     *
     * @param int $languageID
     * @return  Language|null
     */
    public function getLanguage($languageID)
    {
        if (!isset($this->languages[$languageID])) {
            if (!isset($this->cache['languages'][$languageID])) {
                return null;
            }

            $this->languages[$languageID] = $this->cache['languages'][$languageID];
        }

        return $this->languages[$languageID];
    }

    /**
     * Returns the preferred language of the current user.
     *
     * @param int $languageID
     * @return  Language
     */
    public function getUserLanguage($languageID = 0)
    {
        if ($languageID) {
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
     *
     * @param string $languageCode
     * @return  Language
     */
    public function getLanguageByCode($languageCode)
    {
        // called within WCFSetup
        if ($this->cache === false || empty($this->cache['codes'])) {
            $sql = "SELECT  languageID
                    FROM    wcf1_language
                    WHERE   languageCode = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$languageCode]);
            $row = $statement->fetchArray();
            if (isset($row['languageID'])) {
                return new Language($row['languageID']);
            }
        } elseif (isset($this->cache['codes'][$languageCode])) {
            return $this->getLanguage($this->cache['codes'][$languageCode]);
        }

        return null;
    }

    /**
     * Returns true if the language category with the given name exists.
     *
     * @param string $categoryName
     * @return  bool
     */
    public function isValidCategory($categoryName)
    {
        return isset($this->cache['categories'][$categoryName]);
    }

    /**
     * Returns the language category with the given name.
     *
     * @param string $categoryName
     * @return  LanguageCategory|null
     */
    public function getCategory($categoryName)
    {
        return $this->cache['categories'][$categoryName] ?? null;
    }

    /**
     * Returns language category by id.
     *
     * @param int $languageCategoryID
     * @return  LanguageCategory|null
     */
    public function getCategoryByID($languageCategoryID)
    {
        if (isset($this->cache['categoryIDs'][$languageCategoryID])) {
            return $this->cache['categories'][$this->cache['categoryIDs'][$languageCategoryID]];
        }

        return null;
    }

    /**
     * Returns a list of available language categories.
     *
     * @return  LanguageCategory[]
     */
    public function getCategories()
    {
        return $this->cache['categories'];
    }

    /**
     * Searches the preferred language of the current user.
     */
    protected function findPreferredLanguage()
    {
        // get available language codes
        $availableLanguageCodes = [];
        foreach ($this->getLanguages() as $language) {
            $availableLanguageCodes[] = $language->languageCode;
        }

        // get default language
        $defaultLanguageCode = $this->cache['languages'][$this->cache['default']]->languageCode;

        // get preferred language
        $languageCode = self::getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode);

        // get language id of preferred language
        foreach ($this->cache['languages'] as $key => $language) {
            if ($language->languageCode == $languageCode) {
                return $key;
            }
        }
    }

    /**
     * Determines the preferred language of the current user.
     *
     * @param string[] $availableLanguageCodes
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
     *
     * @return  TemplateScriptingCompiler
     */
    public function getScriptingCompiler()
    {
        if ($this->scriptingCompiler === null) {
            $this->scriptingCompiler = new TemplateScriptingCompiler(WCF::getTPL());
        }

        return $this->scriptingCompiler;
    }

    /**
     * Loads the language cache.
     */
    protected function loadCache()
    {
        $this->cache = LanguageCacheBuilder::getInstance()->getData();
    }

    /**
     * Clears languages cache.
     */
    public function clearCache()
    {
        LanguageCacheBuilder::getInstance()->reset();
    }

    /**
     * Removes additional language identifier from given language code.
     * Converts e.g. 'de-informal' to 'de'.
     *
     * @param string $languageCode
     * @return  string      $languageCode
     */
    public static function fixLanguageCode($languageCode)
    {
        return \preg_replace('/-[a-z0-9]+/', '', $languageCode);
    }

    /**
     * Returns the default language object.
     *
     * @return  Language
     * @since   3.0
     */
    public function getDefaultLanguage()
    {
        return $this->getLanguage($this->cache['default']);
    }

    /**
     * Returns the default language id
     *
     * @return  int
     */
    public function getDefaultLanguageID()
    {
        return $this->cache['default'];
    }

    /**
     * Returns all available languages.
     *
     * @return  Language[]
     */
    public function getLanguages()
    {
        return $this->cache['languages'];
    }

    /**
     * Returns all available content languages for given package.
     *
     * @return  Language[]
     */
    public function getContentLanguages()
    {
        $availableLanguages = [];
        foreach ($this->getLanguages() as $languageID => $language) {
            if ($language->hasContent) {
                $availableLanguages[$languageID] = $language;
            }
        }

        return $availableLanguages;
    }

    /**
     * Returns the list of content language ids.
     *
     * @return      int[]
     * @since       3.1
     */
    public function getContentLanguageIDs()
    {
        $languageIDs = [];
        foreach ($this->getLanguages() as $language) {
            if ($language->hasContent) {
                $languageIDs[] = $language->languageID;
            }
        }

        return $languageIDs;
    }

    /**
     * Makes given language the default language.
     *
     * @param int $languageID
     */
    public function makeDefault($languageID)
    {
        // remove old default language
        $sql = "UPDATE  wcf1_language
                SET     isDefault = 0
                WHERE   isDefault = 1";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        // make this language to default
        $sql = "UPDATE  wcf1_language
                SET     isDefault = 1
                WHERE   languageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$languageID]);

        // rebuild language cache
        $this->clearCache();
    }

    /**
     * Removes language cache and compiled templates.
     */
    public function deleteLanguageCache()
    {
        LanguageEditor::deleteLanguageFiles();

        foreach ($this->cache['languages'] as $language) {
            $languageEditor = new LanguageEditor($language);
            $languageEditor->deleteCompiledTemplates();
        }
    }

    /**
     * Returns true if multilingualism is enabled.
     *
     * @return  bool
     */
    public function multilingualismEnabled()
    {
        return $this->cache['multilingualismEnabled'];
    }

    /**
     * Returns the number of phrases that have been automatically disabled in the past 7 days.
     *
     * @return      int
     */
    public function countRecentlyDisabledCustomValues()
    {
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf1_language_item
                WHERE   languageCustomItemDisableTime >= ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([TIME_NOW - 86400 * 7]);

        return $statement->fetchSingleColumn();
    }
}
