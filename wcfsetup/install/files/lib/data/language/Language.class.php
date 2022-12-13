<?php

namespace wcf\data\language;

use wcf\data\DatabaseObject;
use wcf\data\devtools\missing\language\item\DevtoolsMissingLanguageItemAction;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a language.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $languageID     unique id of the language
 * @property-read   string $languageCode       code of the language according to ISO 639-1
 * @property-read   string $languageName       name of the language within the language itself
 * @property-read   string $countryCode        code of the country using the language according to ISO 3166-1, used to determine the language's country flag
 * @property-read   int $isDefault      is `1` if the language is the default language, otherwise `0`
 * @property-read   int $hasContent     is `1` if the language can be selected when creating language-specific content, otherwise `0`
 * @property-read   int $isDisabled     is `1` if the language is disabled and thus not selectable, otherwise `0`
 * @property-read string $locale IETF language tag (BCP 47)
 */
class Language extends DatabaseObject
{
    /**
     * list of language items
     * @var string[]
     */
    protected $items = [];

    /**
     * list of dynamic language items
     * @var string[]
     */
    protected $dynamicItems = [];

    /**
     * instance of LanguageEditor
     * @var LanguageEditor
     */
    private $editor;

    /**
     * id of the active package
     * @var int
     */
    public $packageID = PACKAGE_ID;

    /**
     * contains categories currently being loaded as array keys
     * @var bool[]
     */
    protected $categoriesBeingLoaded = [];

    /**
     * Returns the name of this language.
     */
    public function __toString(): string
    {
        return $this->languageName;
    }

    /**
     * Returns the fixed language code of this language.
     */
    public function getFixedLanguageCode(): string
    {
        return LanguageFactory::fixLanguageCode($this->languageCode);
    }

    /**
     * Returns the page direction of this language.
     */
    public function getPageDirection(): string
    {
        return $this->get('wcf.global.pageDirection');
    }

    /**
     * Returns a single language variable.
     */
    public function get(string $item, bool $optional = false): string
    {
        if (!isset($this->items[$item])) {
            // load category file
            $explodedItem = \explode('.', $item);
            if (\count($explodedItem) < 3) {
                return $item;
            }

            // attempt to load the most specific category
            $this->loadCategory($explodedItem[0] . '.' . $explodedItem[1] . '.' . $explodedItem[2]);
            if (!isset($this->items[$item])) {
                $this->loadCategory($explodedItem[0] . '.' . $explodedItem[1]);
            }
        }

        // return language variable
        if (isset($this->items[$item])) {
            return $this->items[$item];
        }

        // do not output value if there was no match and the item looks like a valid language item
        if ($optional && \preg_match('~^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$~', $item)) {
            return '';
        }

        if (
            \defined('ENABLE_DEVELOPER_TOOLS')
            && ENABLE_DEVELOPER_TOOLS
            && \defined('LOG_MISSING_LANGUAGE_ITEMS')
            && LOG_MISSING_LANGUAGE_ITEMS
            && \preg_match('~^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$~', $item)
        ) {
            (new DevtoolsMissingLanguageItemAction([], 'logLanguageItem', [
                'language' => $this,
                'languageItem' => $item,
            ]))->executeAction();
        }

        // return plain input
        return $item;
    }

    /**
     * Executes template scripting in a language variable.
     */
    public function getDynamicVariable(string $item, array $variables = [], bool $optional = false): string
    {
        $staticItem = $this->get($item, $optional);
        if (!$staticItem) {
            return '';
        }

        if (isset($this->dynamicItems[$item])) {
            // assign active language
            $variables['__language'] = $this;

            return WCF::getTPL()->fetchString($this->dynamicItems[$item], $variables);
        }

        if (
            \defined('ENABLE_DEVELOPER_TOOLS')
            && ENABLE_DEVELOPER_TOOLS
            && \defined('LOG_MISSING_LANGUAGE_ITEMS')
            && LOG_MISSING_LANGUAGE_ITEMS
            && $staticItem === $item
            && \preg_match('~^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$~', $item)
        ) {
            (new DevtoolsMissingLanguageItemAction([], 'logLanguageItem', [
                'language' => $this,
                'languageItem' => $item,
            ]))->executeAction();
        }

        return $staticItem;
    }

    /**
     * Shortcut method to reduce the code repetition in the compiled template code.
     *
     * @param mixed[] $tagStackData
     * @since 5.2
     */
    public function tplGet(string $item, array &$tagStackData): string
    {
        $optional = !empty($tagStackData['__optional']);

        if (!empty($tagStackData['__literal'])) {
            $value = $this->get($item, $optional);
        } else {
            $value = $this->getDynamicVariable($item, $tagStackData, $optional);
        }

        if (!empty($tagStackData['__encode'])) {
            return StringUtil::encodeHTML($value);
        }

        return $value;
    }

    /**
     * Loads category files.
     */
    protected function loadCategory(string $category): bool
    {
        if (!LanguageFactory::getInstance()->isValidCategory($category)) {
            return false;
        }

        // search language file
        $filename = WCF_DIR . 'language/' . $this->languageID . '_' . $category . '.php';
        if (!@\file_exists($filename)) {
            if (isset($this->categoriesBeingLoaded[$category])) {
                throw new \LogicException("Circular dependency detected! Cannot load category '{$category}' while it is already being loaded.");
            }

            if ($this->editor === null) {
                $this->editor = new LanguageEditor($this);
            }

            // rebuild language file
            $languageCategory = LanguageFactory::getInstance()->getCategory($category);
            if ($languageCategory === null) {
                return false;
            }

            $this->categoriesBeingLoaded[$category] = true;

            $this->editor->updateCategory($languageCategory);

            unset($this->categoriesBeingLoaded[$category]);
        }

        // include language file
        @include_once($filename);

        return true;
    }

    /**
     * Returns true if given items includes template scripting.
     */
    public function isDynamicItem(string $item): bool
    {
        if (isset($this->dynamicItems[$item])) {
            return true;
        }

        return false;
    }

    /**
     * Returns language icon path.
     */
    public function getIconPath(): string
    {
        return WCF::getPath() . 'icon/flag/' . $this->countryCode . '.svg';
    }

    /**
     * Returns a list of available languages.
     *
     * @return  Language[]
     */
    public function getLanguages()
    {
        return LanguageFactory::getInstance()->getLanguages();
    }

    /**
     * Sets the package id when a language object is unserialized.
     */
    public function __wakeup()
    {
        $this->packageID = PACKAGE_ID;
    }

    /**
     * Returns true if this language can be deleted.
     *
     * @since   5.4
     */
    public function isDeletable(): bool
    {
        return !$this->isDefault && $this->languageCode !== 'de' && $this->languageCode !== 'en';
    }

    /**
     * Returns the selected locale or if empty the set language code.
     *
     * @since 6.0
     */
    public function getLocale(): string
    {
        return $this->locale ?: $this->languageCode;
    }

    /**
     * Returns a BCP 47 compliant identifier.
     *
     * @since 6.0
     */
    public function getBcp47(): string
    {
        // PHP uses underscores in the region identifier, but HTML/JS expects a dash.
        return \str_replace('_', '-', $this->getLocale());
    }

    /**
     * Returns the name and relative path to the preload cache file.
     *
     * @since 6.0
     */
    public function getPreloadCacheFilename(): string
    {
        return \sprintf(
            'js/preload/%s.preload.js',
            $this->getLocale(),
        );
    }
}
