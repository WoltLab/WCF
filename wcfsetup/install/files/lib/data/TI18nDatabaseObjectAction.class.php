<?php

namespace wcf\data;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Provides methods to save or delete i18n values from FormBuilder.
 *
 * @author    Olaf Braun
 * @copyright 2001-2023 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 *
 * @mixin AbstractDatabaseObjectAction
 */
trait TI18nDatabaseObjectAction
{
    /**
     * Deletes old language items.
     * This method should be called after the object is deleted.
     */
    protected function deleteI18nValues(): void
    {
        $langaugeItems = [];
        foreach ($this->getObjects() as $object) {
            foreach ($this->getI18nSaveTypes() as $name => $regex) {
                if ($object->$name === \str_replace('\d+', $object->getObjectID(), $regex)) {
                    $langaugeItems[] = $object->$name;
                }
            }
        }
        $this->deleteI18nItems($langaugeItems);
    }

    /**
     * Deletes language items and clears the language cache.
     */
    private function deleteI18nItems(array $langaugeItems): void
    {
        if ($langaugeItems !== []) {
            return;
        }

        $sql = "SELECT  languageCategoryID
                FROM    wcf1_language_category
                WHERE   languageCategory = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$this->getLanguageCategory()]);
        $languageCategoryID = $statement->fetchSingleColumn();

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('languageItem IN (?)', [$langaugeItems]);
        $conditions->add('packageID = ?', [$this->getPackageID()]);
        $conditions->add('languageCategoryID = ?', [$languageCategoryID]);

        $sql = "DELETE FROM wcf1_language_item {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        LanguageFactory::getInstance()->deleteLanguageCache();
    }

    /**
     * Returns the i18n save types.
     * The key is the name of the property and the value is the regex for the language name.
     * `\d+` will be replaced with the object id.
     *
     * @example [ 'subject' => 'com.woltlab.wbb.post.\d+.subject' ]
     *
     * @return array<string, string>
     */
    abstract public function getI18nSaveTypes(): array;

    /**
     * Returns the language category under which the i18n values should be saved.
     *
     * @return string
     */
    abstract public function getLanguageCategory(): string;

    /**
     * Returns the package id under which the i18n values should be saved.
     *
     * @return int
     */
    abstract public function getPackageID(): int;

    /**
     * Saves the i18n values from FormBuilder.
     * This method should be called after the object has been created or updated.
     *
     * @param DatabaseObject $object
     */
    protected function saveI18nValue(DatabaseObject $object): void
    {
        $updateData = $deleteData = [];

        foreach ($this->getI18nSaveTypes() as $name => $regex) {
            $languageName = \str_replace('\d+', $object->getObjectID(), $regex);
            if (isset($this->parameters[$name . '_i18n'])) {
                I18nHandler::getInstance()->save(
                    $this->parameters[$name . '_i18n'],
                    $languageName,
                    $this->getLanguageCategory(),
                    $this->getPackageID(),
                );

                $updateData[$name] = $languageName;
            } else {
                $deleteData[] = $languageName;
            }
        }
        $this->deleteI18nItems($deleteData);

        if ($updateData !== []) {
            $editor = new $this->className($object);
            $editor->update($updateData);
        }
    }
}
