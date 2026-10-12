<?php

namespace wcf\data\user\option\category;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\util\StringUtil;

/**
 * Builder for creating, updating and deleting user option categories.
 *
 * The category's localized title is stored in the
 * `wcf1_user_option_category_l10n` table (see `L10nStorage`).
 * `l10nIdentifier` links a system category to its language variable; it is
 * `NULL` for categories created by an administrator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserOptionCategory>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class UserOptionCategoryBuilder extends DatabaseObjectBuilder
{
    private bool $isGenericCategoryName = false;

    /**
     * @var L10nValue
     */
    private array $l10nTitle;

    /**
     * @param L10nValue $title
     */
    public function setL10nTitle(array $title): static
    {
        $this->l10nTitle = $title;

        return $this;
    }

    public function setL10nIdentifier(?string $l10nIdentifier): static
    {
        $this->properties['l10nIdentifier'] = $l10nIdentifier;

        return $this;
    }

    public function setPackageID(int $packageID): static
    {
        $this->properties['packageID'] = $packageID;

        return $this;
    }

    public function setCategoryName(string $categoryName): static
    {
        if ($this->isUpdate() && !\str_starts_with($this->getObject()->categoryName, 'tmp_')) {
            throw new \BadMethodCallException('setCategoryName() is only allowed for generic category names.');
        }

        $this->properties['categoryName'] = $categoryName;

        return $this;
    }

    /**
     * Inserts the category with a temporary random name and renames it to
     * `category<categoryID>` once the id is known (see `afterCreate()`).
     */
    public function setGenericCategoryName(): static
    {
        if ($this->isUpdate()) {
            throw new \BadMethodCallException('setGenericCategoryName() can only be used with forCreate().');
        }

        $this->properties['categoryName'] = 'tmp_' . StringUtil::getRandomID();
        $this->isGenericCategoryName = true;

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

    #[\Override]
    protected function afterValidateCreate(): void
    {
        // System categories receive their title from the language variable
        // through `SyncL10nLanguageItems`.
        if (!isset($this->l10nTitle) && ($this->properties['l10nIdentifier'] ?? null) === null) {
            throw new \BadMethodCallException("Missing value for 'title'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        // assign the generic category name now that the id is known
        if ($this->isGenericCategoryName) {
            UserOptionCategoryBuilder::forUpdate($object)
                ->setCategoryName('category' . $object->categoryID)
                ->update();
        }

        if (isset($this->l10nTitle)) {
            $this->saveL10nValues($object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->l10nTitle)) {
            $this->saveL10nValues($object);
        }
    }

    private function saveL10nValues(UserOptionCategory $category): void
    {
        (new L10nStorage(UserOptionCategory::getL10nDefinition()))->setValues(
            $category->categoryID,
            [
                'title' => $this->l10nTitle,
            ],
        );
    }
}
