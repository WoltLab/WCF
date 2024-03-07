<?php

namespace wcf\data\category;

use wcf\data\IPermissionObject;
use wcf\data\object\type\ObjectType;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\user\User;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\category\ICategoryType;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a category.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $categoryID     unique id of the category
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.category` object type
 * @property-read   int $parentCategoryID   id of the category's parent category or `0` if it has no parent category
 * @property-read   string $title          title of the category or name of language item which contains the title
 * @property-read   string $description        description of the category or name of language item which contains the description
 * @property-read   int $descriptionUseHtml is `1` if html is enabled in the description, otherwise `0`
 * @property-read   int $showOrder      position of the category in relation to its siblings
 * @property-read   int $time           timestamp at which the comment has been created
 * @property-read   int $isDisabled     is `1` if the category is disabled and thus neither accessible nor selectable, otherwise `0`
 * @property-read   array $additionalData     array with additional data of the category
 */
class Category extends ProcessibleDatabaseObject implements IPermissionObject, IRouteController
{
    /**
     * list of child categories of this category
     * @var Category[]
     */
    protected $childCategories;

    /**
     * list of all child categories of this category
     * @var Category[]
     */
    protected $allChildCategories;

    /**
     * list of all parent category generations of this category
     * @var Category[]
     */
    protected $parentCategories;

    /**
     * parent category of this category
     * @var Category
     */
    protected $parentCategory;

    /**
     * acl permissions of this category grouped by the id of the user they
     * belong to
     * @var array
     */
    protected $userPermissions = [];

    /**
     * fallback return value used in Category::getPermission()
     * @var bool
     */
    protected $defaultPermission = false;

    /**
     * @inheritDoc
     */
    protected static $processorInterface = ICategoryType::class;

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        // forward 'className' property requests to object type
        if ($name == 'className') {
            return $this->getObjectType()->className;
        }

        $value = parent::__get($name);

        // check additional data
        if ($value === null) {
            if (isset($this->data['additionalData'][$name])) {
                $value = $this->data['additionalData'][$name];
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        return parent::__isset($name) || isset($this->data['additionalData'][$name]);
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->getPermission($permission)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Returns the category object type of the category.
     *
     * @return  ObjectType
     */
    public function getObjectType()
    {
        return CategoryHandler::getInstance()->getObjectType($this->objectTypeID);
    }

    /**
     * Returns the direct child categories of this category.
     *
     * @return  Category[]
     */
    public function getChildCategories()
    {
        if ($this->childCategories === null) {
            $this->childCategories = CategoryHandler::getInstance()->getChildCategories($this->categoryID);
        }

        return $this->childCategories;
    }

    /**
     * Returns the child categories of this category recursively.
     *
     * @return  Category[]
     */
    public function getAllChildCategories()
    {
        if ($this->allChildCategories === null) {
            $directChildCategories = CategoryHandler::getInstance()->getChildCategories($this->categoryID);
            $childCategories = [];
            foreach ($directChildCategories as $childCategory) {
                $childCategories = \array_replace($childCategories, $childCategory->getAllChildCategories());
            }

            $this->allChildCategories = \array_replace($directChildCategories, $childCategories);
        }

        return $this->allChildCategories;
    }

    /**
     * Returns the parent category of the category or `null` if the category has no parent category.
     *
     * @return  Category|null
     */
    public function getParentCategory()
    {
        if ($this->parentCategoryID && $this->parentCategory === null) {
            $this->parentCategory = CategoryHandler::getInstance()->getCategory($this->parentCategoryID);
        }

        return $this->parentCategory;
    }

    /**
     * Returns the parent categories of this category.
     *
     * @return  Category[]
     */
    public function getParentCategories()
    {
        if ($this->parentCategories === null) {
            $this->parentCategories = [];
            $parentCategory = $this;
            while ($parentCategory = $parentCategory->getParentCategory()) {
                $this->parentCategories[] = $parentCategory;
            }

            $this->parentCategories = \array_reverse($this->parentCategories);
        }

        return $this->parentCategories;
    }

    /**
     * Returns true if given category is a parent category of this category.
     *
     * @param Category $category
     * @return  bool
     */
    public function isParentCategory(self $category)
    {
        return \in_array($category, $this->getParentCategories());
    }

    /**
     * @inheritDoc
     */
    public function getPermission($permission, ?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        if (!isset($this->userPermissions[$user->userID])) {
            $this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()
                ->getPermissions($this, $user);
        }

        if (isset($this->userPermissions[$user->userID][$permission])) {
            return $this->userPermissions[$user->userID][$permission];
        }

        if ($this->getParentCategory()) {
            return $this->getParentCategory()->getPermission($permission, $user);
        }

        if ($this->getObjectType()->defaultpermission !== null) {
            return $this->getObjectType()->defaultpermission ? true : false;
        }

        return $this->defaultPermission;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns the description of this category.
     *
     * @return  string
     */
    public function getDescription()
    {
        if ($this->description) {
            return WCF::getLanguage()->get($this->description);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        // handle additional data
        if (isset($data['additionalData'])) {
            $data['additionalData'] = @\unserialize($data['additionalData']);
            if (!\is_array($data['additionalData'])) {
                $data['additionalData'] = [];
            }
        } else {
            $data['additionalData'] = [];
        }

        parent::handleData($data);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
