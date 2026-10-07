<?php

namespace wcf\data\user\option\category;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Builder for creating, updating and deleting user option categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserOptionCategory>
 */
final class UserOptionCategoryBuilder extends DatabaseObjectBuilder
{
    public function setPackageID(int $packageID): static
    {
        $this->properties['packageID'] = $packageID;

        return $this;
    }

    public function setCategoryName(string $categoryName): static
    {
        $this->properties['categoryName'] = $categoryName;

        return $this;
    }

    /**
     * Sets the name of the parent category, an empty string for top-level categories.
     */
    public function setParentCategoryName(string $parentCategoryName): static
    {
        $this->properties['parentCategoryName'] = $parentCategoryName;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    /**
     * Sets the comma-separated list of permissions of which the active user
     * needs at least one to access the category.
     */
    public function setPermissions(string $permissions): static
    {
        $this->properties['permissions'] = $permissions;

        return $this;
    }

    /**
     * Sets the comma-separated list of options of which at least one needs to
     * be enabled to access the category.
     */
    public function setOptions(string $options): static
    {
        $this->properties['options'] = $options;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['packageID', 'categoryName'];
    }

    public static function resetCache(): void
    {
        UserOptionCacheBuilder::getInstance()->reset();
    }
}
