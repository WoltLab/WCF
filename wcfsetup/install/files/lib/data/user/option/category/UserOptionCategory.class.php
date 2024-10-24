<?php

namespace wcf\data\user\option\category;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\WCF;

/**
 * Represents a user option category.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $categoryID     unique id of the user option category
 * @property-read   int $packageID      id of the package which delivers the user option category
 * @property-read   string $categoryName       name and textual identifier of the user option category
 * @property-read   string $parentCategoryName name of the user option category's parent category or empty if it has no parent category
 * @property-read   int $showOrder      position of the user option category in relation to its siblings
 * @property-read   string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to see the user option category
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the user option category to be shown
 */
class UserOptionCategory extends DatabaseObject implements ITitledObject
{
    /**
     * Returns the title of this category.
     */
    public function __toString(): string
    {
        return $this->categoryName;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.user.option.category.' . $this->categoryName);
    }

    /**
     * Returns an instance of UserOptionCategory by name.
     *
     * @param string $categoryName
     * @return  UserOptionCategory|null
     */
    public static function getCategoryByName($categoryName)
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
