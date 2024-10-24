<?php

namespace wcf\data\article\category;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\label\LabelHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents an article category.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method      ArticleCategory[]   getChildCategories()
 * @method      ArticleCategory[]   getAllChildCategories()
 * @method      ArticleCategory     getParentCategory()
 * @method      ArticleCategory[]   getParentCategories()
 * @method static ArticleCategory|null    getCategory($categoryID)
 * @property-read       string $sortField
 * @property-read       string $sortOrder
 */
class ArticleCategory extends AbstractDecoratedCategory implements IAccessibleObject, ITitledLinkObject
{
    /**
     * object type name of the article categories
     * @var string
     */
    const OBJECT_TYPE_NAME = 'com.woltlab.wcf.article.category';

    /**
     * acl permissions of this category grouped by the id of the user they
     * belong to
     * @var array
     */
    protected $userPermissions = [];

    /**
     * subscribed categories
     * @var int[]
     */
    protected static $subscribedCategories;

    /**
     * @inheritDoc
     */
    public function isAccessible(?User $user = null)
    {
        if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
            return false;
        }

        if ($this->isDisabled) {
            return false;
        }

        // check permissions
        return $this->getPermission('canReadArticle', $user);
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
            $this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions(
                $this->getDecoratedObject(),
                $user
            );
        }

        if (isset($this->userPermissions[$user->userID][$permission])) {
            return $this->userPermissions[$user->userID][$permission];
        }

        if ($this->getParentCategory()) {
            return $this->getParentCategory()->getPermission($permission, $user);
        }

        if ($user->userID === WCF::getSession()->getUser()->userID) {
            return WCF::getSession()->getPermission('user.article.' . $permission);
        } else {
            $userProfile = new UserProfile($user);

            return $userProfile->getPermission('user.article.' . $permission);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('CategoryArticleList', [
            'forceFrontend' => true,
            'object' => $this->getDecoratedObject(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns a list with ids of accessible categories.
     *
     * @param string[] $permissions
     * @return  int[]
     */
    public static function getAccessibleCategoryIDs(array $permissions = ['canReadArticle'])
    {
        $categoryIDs = [];
        foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
            $category = new self($category);

            if (!$category->isDisabled) {
                $result = true;
                foreach ($permissions as $permission) {
                    $result = $result && $category->getPermission($permission);
                }

                if ($result) {
                    $categoryIDs[] = $category->categoryID;
                }
            }
        }

        return $categoryIDs;
    }

    /**
     * Returns the label groups for all accessible categories.
     *
     * @param string $permission
     * @return  ViewableLabelGroup[]
     */
    public static function getAccessibleLabelGroups($permission = 'canSetLabel')
    {
        $labelGroupsToCategories = ArticleCategoryLabelCacheBuilder::getInstance()->getData();
        $accessibleCategoryIDs = self::getAccessibleCategoryIDs();

        $groupIDs = [];
        foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
            if (\in_array($categoryID, $accessibleCategoryIDs)) {
                $groupIDs = \array_merge($groupIDs, $__groupIDs);
            }
        }
        if (empty($groupIDs)) {
            return [];
        }

        return LabelHandler::getInstance()->getLabelGroups(\array_unique($groupIDs), true, $permission);
    }

    /**
     * Returns the label groups for this category.
     *
     * @return      ViewableLabelGroup[]
     * @since       5.4
     */
    public function getLabelGroups(string $permission = 'canSetLabel'): array
    {
        $labelGroupsToCategories = ArticleCategoryLabelCacheBuilder::getInstance()->getData();

        if (isset($labelGroupsToCategories[$this->categoryID])) {
            return LabelHandler::getInstance()->getLabelGroups(
                $labelGroupsToCategories[$this->categoryID],
                true,
                $permission
            );
        }

        return [];
    }

    /**
     * Returns all userIDs which have subscribed this category.
     *
     * @return  int[]
     * @since   5.5
     */
    public function getSubscribedUserIDs(): array
    {
        $subscribers = UserObjectWatchHandler::getInstance()->getSubscribers(
            'com.woltlab.wcf.article.category',
            $this->categoryID
        );

        return \array_keys($subscribers);
    }

    /**
     * Returns true if the active user has subscribed to this category.
     *
     * @return  bool
     * @since       5.2
     */
    public function isSubscribed()
    {
        return \in_array($this->categoryID, self::getSubscribedCategoryIDs());
    }

    /**
     * Returns the list of subscribed categories.
     *
     * @return  int[]
     * @since       5.2
     */
    public static function getSubscribedCategoryIDs()
    {
        if (self::$subscribedCategories === null) {
            self::$subscribedCategories = [];

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField('articleSubscribedCategories');

                // cache does not exist or is outdated
                if ($data === null) {
                    $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article.category');

                    $sql = "SELECT  objectID
                            FROM    wcf1_user_object_watch
                            WHERE   objectTypeID = ?
                                AND userID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([$objectTypeID, WCF::getUser()->userID]);
                    self::$subscribedCategories = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'articleSubscribedCategories',
                        \serialize(self::$subscribedCategories)
                    );
                } else {
                    self::$subscribedCategories = \unserialize($data);
                }
            }
        }

        return self::$subscribedCategories;
    }
}
