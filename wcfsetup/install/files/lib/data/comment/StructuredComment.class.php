<?php

namespace wcf\data\comment;

use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Provides methods to handle responses for this comment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Comment
 *
 * @method  Comment     getDecoratedObject()
 * @mixin   Comment
 */
class StructuredComment extends DatabaseObjectDecorator implements \Countable, \Iterator
{
    /**
     * @inheritDoc
     */
    public static $baseClass = Comment::class;

    /**
     * list of ordered responses
     * @var StructuredCommentResponse[]
     */
    protected $responses = [];

    /**
     * deletable by current user
     * @var bool
     */
    public $deletable = false;

    /**
     * editable by current user
     * @var bool
     */
    public $editable = false;

    /**
     * iterator index
     * @var int
     */
    private $position = 0;

    /**
     * user profile object of the comment author
     * @var UserProfile
     */
    public $userProfile;

    /**
     * Adds an response
     *
     * @param StructuredCommentResponse $response
     */
    public function addResponse(StructuredCommentResponse $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Returns the last responses for this comment.
     *
     * @return  StructuredCommentResponse[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Returns timestamp of oldest response loaded.
     *
     * @return  int
     */
    public function getLastResponseTime()
    {
        $lastResponseTime = 0;
        foreach ($this->responses as $response) {
            if (!$lastResponseTime) {
                $lastResponseTime = $response->time;
            }

            $lastResponseTime = \max($lastResponseTime, $response->time);
        }

        return $lastResponseTime;
    }

    /**
     * Sets the user's profile.
     *
     * @param UserProfile $userProfile
     * @deprecated  3.0
     */
    public function setUserProfile(UserProfile $userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * Returns the user's profile.
     *
     * @return  UserProfile
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            if ($this->userID) {
                $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
            } else {
                $this->userProfile = UserProfile::getGuestUserProfile($this->username);
            }
        }

        return $this->userProfile;
    }

    /**
     * Sets deletable state.
     *
     * @param bool $deletable
     */
    public function setIsDeletable($deletable)
    {
        $this->deletable = $deletable;
    }

    /**
     * Sets editable state.
     *
     * @param bool $editable
     */
    public function setIsEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * Returns true if the comment is deletable by current user.
     *
     * @return  bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * Returns true if the comment is editable by current user.
     *
     * @return  bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return \count($this->responses);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->responses[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->responses[$this->position]);
    }
}
