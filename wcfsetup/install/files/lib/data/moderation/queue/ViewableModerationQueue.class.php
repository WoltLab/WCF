<?php

namespace wcf\data\moderation\queue;

use wcf\action\ModerationQueueAssignUserAction;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\data\IUserContent;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a viewable moderation queue entry.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ModerationQueue     getDecoratedObject()
 * @mixin   ModerationQueue
 */
class ViewableModerationQueue extends DatabaseObjectDecorator implements ILinkableObject, ITitledObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModerationQueue::class;

    /**
     * affected object
     * @var IUserContent
     */
    protected $affectedObject;

    /**
     * true, if associated object was deleted
     * @var bool
     */
    protected $isOrphaned = false;

    /**
     * user profile object
     * @var UserProfile
     */
    protected $userProfile;

    /**
     * Sets link for viewing/editing.
     *
     * @param IUserContent $object
     */
    public function setAffectedObject(IUserContent $object)
    {
        $this->affectedObject = $object;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return ModerationQueueManager::getInstance()->getLink($this->objectTypeID, $this->queueID);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->affectedObject === null ? '' : $this->affectedObject->getTitle();
    }

    /**
     * Returns affected object.
     *
     * @return  IUserContent
     */
    public function getAffectedObject()
    {
        return $this->affectedObject;
    }

    /**
     * Sets associated user profile object.
     *
     * @param UserProfile $userProfile
     * @deprecated  3.0
     */
    public function setUserProfile(UserProfile $userProfile)
    {
        if ($this->affectedObject !== null && ($userProfile->userID == $this->affectedObject->getUserID())) {
            $this->userProfile = $userProfile;
        }
    }

    /**
     * Returns associated user profile object.
     *
     * @return  UserProfile
     */
    public function getUserProfile()
    {
        if ($this->affectedObject !== null && $this->userProfile === null) {
            if ($this->affectedObject->getUserID()) {
                $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->affectedObject->getUserID());
            } else {
                $this->userProfile = new UserProfile(new User(null, []));
            }
        }

        return $this->userProfile;
    }

    /**
     * Returns assigned user profile object.
     *
     * @return  UserProfile|null
     */
    public function getAssignedUserProfile()
    {
        if ($this->assignedUserID) {
            return UserProfileRuntimeCache::getInstance()->getObject($this->assignedUserID);
        }

        return null;
    }

    /**
     * Returns true if associated object was removed.
     *
     * @return  bool
     */
    public function isOrphaned()
    {
        return $this->isOrphaned;
    }

    /**
     * Marks associated objects as removed.
     */
    public function setIsOrphaned()
    {
        $this->isOrphaned = true;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Returns a viewable moderation queue entry.
     *
     * @param int $queueID
     * @return  ViewableModerationQueue
     */
    public static function getViewableModerationQueue($queueID)
    {
        $queueList = new ViewableModerationQueueList();
        $queueList->getConditionBuilder()->add("moderation_queue.queueID = ?", [$queueID]);
        $queueList->sqlLimit = 1;
        $queueList->readObjects();

        return $queueList->getSingleObject();
    }

    /**
     * Returns formatted message text.
     *
     * @return  string
     */
    public function getFormattedMessage()
    {
        return SimpleMessageParser::getInstance()->parse($this->message, true, false);
    }

    public function getMailText(string $mimeType = 'text/html'): string
    {
        if ($mimeType === 'text/plain') {
            return StringUtil::stripHTML($this->getFormattedMessage());
        }
        return $this->getFormattedMessage();
    }

    /**
     * Returns the object type name.
     *
     * @return  string
     */
    public function getObjectTypeName()
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType;
    }

    /**
     * Returns true if this queue item is new for the active user.
     *
     * @return  bool
     */
    public function isNew()
    {
        if (
            $this->time > \max(
                VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.moderation.queue'),
                VisitTracker::getInstance()->getObjectVisitTime('com.woltlab.wcf.moderation.queue', $this->queueID)
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the label for this queue entry.
     *
     * @return      string
     */
    public function getLabel()
    {
        $definition = ObjectTypeCache::getInstance()->getDefinition(ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->definitionID);

        /** @noinspection PhpUndefinedFieldInspection */
        if ($definition->definitionName == 'com.woltlab.wcf.moderation.activation' && $this->getAffectedObject()->enableTime) {
            return WCF::getLanguage()->get('wcf.moderation.type.com.woltlab.wcf.moderation.activation.delayed');
        }

        return WCF::getLanguage()->get('wcf.moderation.type.' . $definition->definitionName);
    }

    /**
     * @since 6.0
     */
    public function getIcon(): FontAwesomeIcon
    {
        $definition = ObjectTypeCache::getInstance()->getDefinition(ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->definitionID);

        if ($definition->definitionName === 'com.woltlab.wcf.moderation.activation') {
            if ($this->getAffectedObject()->enableTime) {
                return FontAwesomeIcon::fromValues('clock');
            } else {
                return FontAwesomeIcon::fromValues('square-check');
            }
        }

        return FontAwesomeIcon::fromValues('triangle-exclamation');
    }

    /**
     * @since 6.0
     */
    public function endpointAssignUser(): string
    {
        return LinkHandler::getInstance()->getControllerLink(ModerationQueueAssignUserAction::class, ['object' => $this]);
    }
}
