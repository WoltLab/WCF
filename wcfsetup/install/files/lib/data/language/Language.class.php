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
 * @package WoltLabSuite\Core\Data\Language
 *
 * @property-read   int $languageID     unique id of the language
 * @property-read   string $languageCode       code of the language according to ISO 639-1
 * @property-read   string $languageName       name of the language within the language itself
 * @property-read   string $countryCode        code of the country using the language according to ISO 3166-1, used to determine the language's country flag
 * @property-read   int $isDefault      is `1` if the language is the default language, otherwise `0`
 * @property-read   int $hasContent     is `1` if the language can be selected when creating language-specific content, otherwise `0`
 * @property-read   int $isDisabled     is `1` if the language is disabled and thus not selectable, otherwise `0`
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
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->languageName;
    }

    /**
     * Returns the fixed language code of this language.
     *
     * @return  string
     */
    public function getFixedLanguageCode()
    {
        return LanguageFactory::fixLanguageCode($this->languageCode);
    }

    /**
     * Returns the page direction of this language.
     *
     * @return  string
     */
    public function getPageDirection()
    {
        return $this->get('wcf.global.pageDirection');
    }

    /**
     * Returns a single language variable.
     *
     * @param string $item
     * @param bool $optional
     * @return  string
     */
    public function get($item, $optional = false)
    {
        if (
            \defined('ENABLE_DEBUG_MODE')
            && ENABLE_DEBUG_MODE
            && \defined('ENABLE_DEVELOPER_TOOLS')
            && ENABLE_DEVELOPER_TOOLS
            && \is_array($optional)
            && !empty($optional)
        ) {
            throw new \InvalidArgumentException("The second parameter of Language::get() does not support non-empty arrays. Did you mean to use Language::getDynamicVariable()?");
        }

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
     *
     * @param string $item
     * @param array $variables
     * @param bool $optional
     * @return  string      result
     */
    public function getDynamicVariable($item, array $variables = [], $optional = false)
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
     * @param string $item
     * @param mixed[] $tagStackData
     * @return string
     * @since 5.2
     */
    public function tplGet($item, array &$tagStackData)
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
     *
     * @param string $category
     * @return  bool
     */
    protected function loadCategory($category)
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
     *
     * @param string $item
     * @return  bool
     */
    public function isDynamicItem($item)
    {
        if (isset($this->dynamicItems[$item])) {
            return true;
        }

        return false;
    }

    /**
     * Returns language icon path.
     *
     * @return  string
     */
    public function getIconPath()
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
}
