<?php

namespace wcf\system\interaction\user;

use wcf\action\UserFollowAction;
use wcf\data\DatabaseObject;
use wcf\data\user\UserProfile;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Extended interaction provider for the context menu of user profiles with the option to follow users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserProfileInteractionsWithFollow extends UserProfileInteractions
{
    public function __construct()
    {
        $this->addInteractions([
            new class(
                'follow',
                static fn(UserProfile $user) => !WCF::getUser()->isGuest()
                    && WCF::getUser()->userID !== $user->userID
                    && !UserProfileHandler::getInstance()->isIgnoredByUser($user->userID)
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof UserProfile);

                    $endpoint = StringUtil::encodeHTML(
                        LinkHandler::getInstance()->getControllerLink(UserFollowAction::class, ['id' => $object->userID])
                    );

                    if (UserProfileHandler::getInstance()->isFollowing($object->userID)) {
                        $title = WCF::getLanguage()->get('wcf.user.button.unfollow');

                        return <<<HTML
                            <button
                                type="button"
                                data-following="1"
                                data-follow-user="{$endpoint}"
                                data-type="button"
                            >{$title}</button>
                            HTML;
                    } else {
                        $title = WCF::getLanguage()->get('wcf.user.button.follow');

                        return <<<HTML
                            <button
                                type="button"
                                data-following="0"
                                data-follow-user="{$endpoint}"
                                data-type="button"
                            >{$title}</button>
                            HTML;
                    }
                }
            },
        ]);

        parent::__construct();
    }
}
