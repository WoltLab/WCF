<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Provides methods to handle response data.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Comment\Response
 *
 * @method  CommentResponse     getDecoratedObject()
 * @mixin   CommentResponse
 */
class StructuredCommentResponse extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    public static $baseClass = CommentResponse::class;

    /**
     * deletable by current user
     * @var bool
     */
    public $deletable = false;

    /**
     * editable for current user
     * @var bool
     */
    public $editable = false;

    /**
     * user profile of the comment response author
     * @var UserProfile
     */
    public $userProfile;

    /**
     * Sets the user's profile.
     *
     * @param   UserProfile $userProfile
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
     * Returns a structured response.
     *
     * @param   int     $responseID
     * @return  StructuredCommentResponse
     */
    public static function getResponse($responseID)
    {
        $response = new CommentResponse($responseID);
        if (!$response->responseID) {
            return;
        }

        // prepare structured response
        $response = new self($response);

        // cache user profile
        if ($response->userID) {
            UserProfileRuntimeCache::getInstance()->cacheObjectID($response->userID);
        }

        return $response;
    }

    /**
     * Sets deletable state.
     *
     * @param   bool        $deletable
     */
    public function setIsDeletable($deletable)
    {
        $this->deletable = $deletable;
    }

    /**
     * Sets editable state.
     *
     * @param   bool        $editable
     */
    public function setIsEditable($editable)
    {
        $this->editable = $editable;
    }

    /**
     * Returns true if the response is deletable by current user.
     *
     * @return  bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * Returns true if the response is editable by current user.
     *
     * @return  bool
     */
    public function isEditable()
    {
        return $this->editable;
    }
}
