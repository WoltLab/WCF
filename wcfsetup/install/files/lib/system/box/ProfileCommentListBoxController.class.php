<?php

namespace wcf\system\box;

use wcf\data\comment\ViewableCommentList;
use wcf\data\user\profile\comment\ViewableUserProfileComment;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Box controller implementation for a list of comments on user profiles.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ProfileCommentListBoxController extends AbstractCommentListBoxController
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.woltlab.wcf.user.profileComment';

    /**
     * @inheritDoc
     */
    protected function applyObjectTypeFilters(ViewableCommentList $commentList)
    {
        $commentList->decoratorClassName = ViewableUserProfileComment::class;

        if (WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            $optionID = User::getUserOptionID('canViewProfile');
            $commentList->sqlJoins .= '
                INNER JOIN  wcf1_user_option_value user_option_value
                ON          user_option_value.userID = comment.objectID';

            if (WCF::getUser()->userID) {
                $followers = UserProfileHandler::getInstance()->getFollowers();
                if (empty($followers)) {
                    $commentList->getConditionBuilder()->add("(
                        user_option_value.userOption{$optionID} IN (?)
                        OR user_option_value.userID = ?
                    )", [
                        [
                            UserProfile::ACCESS_EVERYONE,
                            UserProfile::ACCESS_REGISTERED,
                        ],
                        WCF::getUser()->userID,
                    ]);
                } else {
                    $commentList->getConditionBuilder()->add("(
                        user_option_value.userOption{$optionID} IN (?)
                        OR (
                            user_option_value.userOption{$optionID} = ?
                            AND comment.objectID IN (?)
                        )
                        OR user_option_value.userID = ?
                    )", [
                        [
                            UserProfile::ACCESS_EVERYONE,
                            UserProfile::ACCESS_REGISTERED,
                        ],
                        UserProfile::ACCESS_FOLLOWING,
                        $followers,
                        WCF::getUser()->userID,
                    ]);
                }
            } else {
                $commentList->getConditionBuilder()->add(
                    "user_option_value.userOption{$optionID} = ?",
                    [UserProfile::ACCESS_EVERYONE]
                );
            }
        } else {
            $commentList->getConditionBuilder()->add('0 = 1');
        }
    }
}
