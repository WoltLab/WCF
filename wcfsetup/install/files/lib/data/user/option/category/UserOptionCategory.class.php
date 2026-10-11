<?php

namespace wcf\data\user\option\category;

use wcf\data\CollectionDatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Represents a user option category.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $categoryID         unique id of the user option category
 * @property-read   int     $packageID          id of the package which delivers the user option category
 * @property-read   string  $categoryName       name and textual identifier of the user option category
 * @property-read   string  $parentCategoryName name of the user option category's parent category or empty if it has no parent category
 * @property-read   int     $showOrder          position of the user option category in relation to its siblings
 * @property-read   ?string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to see the user option category
 * @property-read   ?string $options            comma separated list of options of which at least one needs to be enabled for the user option category to be shown
 * @property-read   ?string $l10nIdentifier     base name of the language variable the localized title is derived from, `null` for categories created by an administrator
 *
 * @phpstan-import-type L10nValue from L10nStorage
 * @extends CollectionDatabaseObject<UserOptionCategoryCollection>
 */
class UserOptionCategory extends CollectionDatabaseObject implements ITitledObject, \Stringable
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;

    /**
     * Returns the title of this category.
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->categoryName;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'title');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'title') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_user_option_category',
            'wcf1_user_option_category_l10n',
            'categoryID',
            ['title'],
            'l10nIdentifier',
            ['title' => ''],
            ['title' => 255],
        );
    }

    /**
     * Returns true if this category can be deleted, i.e. if it is a category of the user profile
     * and does not contain any user options.
     *
     * @since 6.3
     */
    public function canDelete(): bool
    {
        return $this->parentCategoryName === 'profile'
            && $this->getUserOptionCount() === 0;
    }

    /**
     * Returns the number of user options within this category.
     *
     * @since 6.3
     */
    public function getUserOptionCount(): int
    {
        if (isset($this->data['userOptions'])) {
            return (int)$this->data['userOptions'];
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user_option
                WHERE   categoryName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->categoryName]);

        return (int)$statement->fetchSingleColumn();
    }

    /**
     * Returns an instance of UserOptionCategory by name.
     *
     * @return  UserOptionCategory|null
     */
    public static function getCategoryByName(string $categoryName)
    {
        $sql = "SELECT  *
                FROM    wcf1_user_option_category
                WHERE   categoryName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$categoryName]);
        $row = $statement->fetchArray();
        if ($row === false) {
            return null;
        }

        return new self(null, $row);
    }
}
