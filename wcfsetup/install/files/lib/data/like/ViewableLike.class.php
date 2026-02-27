<?php

namespace wcf\data\like;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Provides methods for viewable likes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Like
 * @extends DatabaseObjectDecorator<Like>
 */
class ViewableLike extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    public static $baseClass = Like::class;

    protected string $description = '';
    protected bool $isAccessible = false;
    protected string $title = '';

    /**
     * description of the object type displayed in the list of likes
     * @var     string
     * @since   3.1
     * @deprecated 6.3 No longer in use.
     */
    protected $objectTypeDescription;

    /**
     * @since 6.3
     */
    protected string $link = '';

    /**
     * Marks this like as accessible for current user.
     */
    public function setIsAccessible(): void
    {
        $this->isAccessible = true;
    }

    /**
     * Returns true if like is accessible by current user.
     */
    public function isAccessible(): bool
    {
        return $this->isAccessible;
    }

    public function getUserProfile(): ?UserProfile
    {
        return UserProfileRuntimeCache::getInstance()->getObject($this->userID);
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the object type name.
     *
     * @return  string
     * @deprecated 6.3 No longer in use.
     */
    public function getObjectTypeName()
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType;
    }

    /**
     * Sets the description of the object type displayed in the list of likes.
     *
     * @param string $name
     * @return void
     * @since   3.1
     * @deprecated 6.3 No longer in use.
     */
    public function setObjectTypeDescription($name)
    {
        $this->objectTypeDescription = $name;
    }

    /**
     * Returns the description of the object type displayed in the list of likes.
     *
     * If no description has been set before, `wcf.like.objectType.{$this->getObjectTypeName()}`
     * is returned.
     *
     * @return  string
     * @since   3.1
     * @deprecated 6.3 No longer in use.
     */
    public function getObjectTypeDescription()
    {
        if ($this->objectTypeDescription !== null) {
            return $this->objectTypeDescription;
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.like.objectType.' . $this->getObjectTypeName());
    }

    /**
     * @since 6.3
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @since 6.3
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @since 6.3
     */
    public function isIgnoredContent(): bool
    {
        return UserProfileHandler::getInstance()->getUserProfile()->isIgnoredUser(
            $this->getUserProfile()->userID,
            UserIgnore::TYPE_HIDE_MESSAGES
        );
    }
}
