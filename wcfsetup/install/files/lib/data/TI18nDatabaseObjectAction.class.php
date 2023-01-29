<?php

namespace wcf\data;

use wcf\system\language\I18nHandler;
use wcf\util\StringUtil;

/**
 * Provides methods for automatic handling of i18n values.
 *
 * @author  Florian Gail
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
trait TI18nDatabaseObjectAction
{
    /**
     * Automatically pre-fills `$propertyName` using the given pattern or a random string if none is given.
     * This function is meant to be used for NOT NULL-columns without default value, which would cause an error
     * when no value is given during the object's creation.
     */
    protected function prefillI18nColumn(
        string $propertyName,
        string $languageItemPattern,
        ?DatabaseObject $object = null,
        ?string $targetArrayIndex = 'data'
    ): void {
        $found = ($targetArrayIndex === null && isset($this->parameters[$propertyName]))
            || ($targetArrayIndex !== null && isset($this->parameters[$targetArrayIndex][$propertyName]));

        if (!isset($this->parameters[$propertyName . '_i18n']) || !$found) {
            return;
        }

        if ($object === null) {
            $languageItem = StringUtil::getRandomID();
        } else {
            $languageItem = \str_replace('\\d+', $object->getObjectID(), $languageItemPattern);
        }

        if ($targetArrayIndex === null) {
            $this->parameters[$propertyName] = $languageItem;
        } else {
            $this->parameters[$targetArrayIndex][$propertyName] = $languageItem;
        }
    }

    /**
     * Automatically saves the i18n data for `$propertyName` and updates the property value using the given pattern
     * and object.
     */
    protected function saveI18nColumn(
        string $propertyName,
        string $languageItemPattern,
        string $languageItemCategory,
        int $packageID,
        DatabaseObject|DatabaseObjectEditor $object
    ): void {
        if (!isset($this->parameters[$propertyName . '_i18n'])) {
            return;
        }

        // make sure to use the original object's property, not something set by the editor
        if ($object instanceof DatabaseObjectEditor) {
            $object = $object->getDecoratedObject();
        }

        $languageItem = \str_replace('\\d+', $object->getObjectID(), $languageItemPattern);

        I18nHandler::getInstance()->save(
            $propertyName,
            $languageItem,
            $languageItemCategory,
            $packageID
        );

        if ($object->$propertyName !== $languageItem) {
            $className = $this->getClassName();
            (new $className($object))->update([$propertyName => $languageItem]);
        }
    }
}
