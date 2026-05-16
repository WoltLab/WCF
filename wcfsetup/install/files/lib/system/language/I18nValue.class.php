<?php

namespace wcf\system\language;

use wcf\data\package\PackageCache;

/**
 * Represents an i18n value for use with `AbstractAcpForm`.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class I18nValue implements \Stringable
{
    /**
     * field name
     * @var string
     */
    protected $fieldName = '';

    /**
     * bit-mask to alter validation rules
     * @var int
     */
    protected $flags = 0;

    /**
     * language item template, placeholder or id will be appended
     * @var string
     */
    protected $languageItem = '';

    /**
     * language item category
     * @var string
     */
    protected $languageItemCategory = '';

    /**
     * package name used for the `packageID` reference
     * @var string
     */
    protected $languageItemPackage = '';

    /**
     * allow an empty value, that includes providing no value at all
     */
    const ALLOW_EMPTY = 1;

    /**
     * require localized values, disallowing plain values
     */
    const REQUIRE_I18N = 2;

    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Sets the language item configuration.
     *
     * @return void
     */
    public function setLanguageItem(string $item, string $category, string $package)
    {
        $this->languageItem = $item;
        $this->languageItemCategory = $category;
        $this->languageItemPackage = $package;
    }

    /**
     * Sets bit flags.
     *
     * @return void
     */
    public function setFlags(int $flags)
    {
        $this->flags = $flags;
    }

    /**
     * Returns true if given flag is set.
     *
     * @return      bool
     */
    public function getFlag(int $flag)
    {
        return ($this->flags & $flag) === $flag;
    }

    /**
     * Returns the field identifier.
     *
     * @return      string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Returns the language item template.
     *
     * @return      string
     */
    public function getLanguageItem()
    {
        return $this->languageItem;
    }

    /**
     * Returns the language category.
     *
     * @return      string
     */
    public function getLanguageCategory()
    {
        return $this->languageItemCategory;
    }

    /**
     * Returns the package id.
     *
     * @return ?int
     */
    public function getPackageID()
    {
        return PackageCache::getInstance()->getPackageID($this->languageItemPackage);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getFieldName();
    }
}
