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
    public function addResponse(StructuredCommentResponse $response): void
    {
        $this->responses[] = $response;
    }

    /**
     * Returns the last responses for this comment.
     *
     * @return  StructuredCommentResponse[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * Returns timestamp of newest response loaded.
     *
     * @return  int
     */
    public function getLastResponseTime(): int
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
     * Returns id of newest response loaded.
     *
     * @since 6.0
     */
    public function getLastResponseID(): int
    {
        if ($this->responses === []) {
            return 0;
        }

        return $this->responses[\count($this->responses) - 1]->responseID;
    }

    /**
     * Sets the user's profile.
     *
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
     */
    public function setIsDeletable(bool $deletable): void
    {
        $this->deletable = $deletable;
    }

    /**
     * Sets editable state.
     */
    public function setIsEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * Returns true if the comment is deletable by current user.
     */
    public function isDeletable(): bool
    {
        return $this->deletable;
    }

    /**
     * Returns true if the comment is editable by current user.
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->responses);
    }

    /**
     * @inheritDoc
     */
    public function current(): StructuredCommentResponse
    {
        return $this->responses[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->responses[$this->position]);
    }
}
