<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Represents a viewable comment response.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   CommentResponse
 * @extends DatabaseObjectDecorator<CommentResponse>
 */
class ViewableCommentResponse extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CommentResponse::class;

    /**
     * user profile of the comment author
     * @var ?UserProfile
     */
    protected $userProfile;

    /**
     * Returns the user profile object.
     *
     * @return  UserProfile
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            if ($this->userID !== null) {
                $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
            } else {
                $this->userProfile = UserProfile::getGuestUserProfile($this->username);
            }
        }

        return $this->userProfile;
    }

    /**
     * Returns a specific comment response decorated as viewable comment response.
     *
     * @return  ViewableCommentResponse
     */
    public static function getResponse(int $responseID)
    {
        $list = new ViewableCommentResponseList();
        $list->setObjectIDs([$responseID]);
        $list->readObjects();

        return $list->getSingleObject();
    }
}
