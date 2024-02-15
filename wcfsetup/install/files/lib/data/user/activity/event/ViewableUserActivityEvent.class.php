<?php

namespace wcf\data\user\activity\event;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\UserProfileHandler;

/**
 * Provides methods for viewable user activity events.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserActivityEvent   getDecoratedObject()
 * @mixin   UserActivityEvent
 */
class ViewableUserActivityEvent extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    public static $baseClass = UserActivityEvent::class;

    /**
     * event text
     * @var string
     */
    protected $description = '';

    /**
     * accessible by current user
     * @var bool
     */
    protected $isAccessible = false;

    /**
     * associated object was removed
     * @var bool
     */
    protected $isOrphaned = false;

    /**
     * event title
     * @var string
     */
    protected $title = '';

    /**
     * user profile
     * @var UserProfile
     */
    protected $userProfile;

    /**
     * true if event description contains raw html
     * @var bool
     */
    protected $isRawHtml = false;

    /**
     * @since 6.1
     */
    protected string $link = '';

    /**
     * Marks this event as accessible for current user.
     */
    public function setIsAccessible()
    {
        $this->isAccessible = true;
    }

    /**
     * Returns true if event is accessible by current user.
     *
     * @return  bool
     */
    public function isAccessible()
    {
        return $this->isAccessible;
    }

    /**
     * Marks this event as orphaned.
     */
    public function setIsOrphaned()
    {
        $this->isOrphaned = true;
    }

    /**
     * Returns true if event is orphaned (associated object removed).
     *
     * @return  bool
     */
    public function isOrphaned()
    {
        return $this->isOrphaned;
    }

    /**
     * Sets user profile.
     *
     * @param UserProfile $userProfile
     * @deprecated  3.0
     */
    public function setUserProfile(UserProfile $userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * Returns user profile.
     *
     * @return  UserProfile
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        }

        return $this->userProfile;
    }

    /**
     * Sets event text.
     *
     * @param string $description
     * @param bool $isRawHtml
     */
    public function setDescription($description, $isRawHtml = false)
    {
        $this->description = $description;
        $this->isRawHtml = $isRawHtml;
    }

    /**
     * Returns event text.
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets event title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns event title.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the object type name.
     *
     * @return  string
     */
    public function getObjectTypeName()
    {
        return UserActivityEventHandler::getInstance()->getObjectType($this->objectTypeID)->objectType;
    }

    /**
     * Returns true if event description contains raw html.
     *
     * @return      bool
     * @since       3.1
     */
    public function isRawHtml()
    {
        return $this->isRawHtml;
    }

    /**
     * @since 6.1
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @since 6.1
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @since 6.1
     */
    public function isIgnoredContent(): bool
    {
        return UserProfileHandler::getInstance()->getUserProfile()->isIgnoredUser($this->getUserProfile()->userID, 2);
    }
}
