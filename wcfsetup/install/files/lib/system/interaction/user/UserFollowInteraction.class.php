<?php

namespace wcf\system\interaction\user;

use wcf\action\UserFollowAction;
use wcf\data\DatabaseObject;
use wcf\data\user\UserProfile;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user follow interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class UserFollowInteraction extends AbstractInteraction
{
    public function __construct()
    {
        parent::__construct('follow');
    }

    #[\Override]
    public function isAvailable(DatabaseObject $object): bool
    {
        \assert($object instanceof UserProfile);

        if (!WCF::getUser()->userID || WCF::getUser()->userID === $object->userID) {
            return false;
        }

        return !UserProfileHandler::getInstance()->isIgnoredByUser($object->userID);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        \assert($object instanceof UserProfile);

        if (UserProfileHandler::getInstance()->isFollowing($object->userID)) {
            $endpoint = StringUtil::encodeHTML(
                LinkHandler::getInstance()->getControllerLink(UserFollowAction::class, ['id' => $object->userID])
            );
            $title = WCF::getLanguage()->get('wcf.user.button.unfollow');
            $icon = FontAwesomeIcon::fromString('circle-minus;true')->toHtml(24);

            return <<<HTML
                <button
                    type="button"
                    data-following="1"
                    data-follow-user="{$endpoint}"
                    class="userCard__button jsTooltip"
                    title="{$title}"
                >{$icon}</button>
                HTML;
        } else {
            $endpoint = StringUtil::encodeHTML(
                LinkHandler::getInstance()->getControllerLink(UserFollowAction::class, ['id' => $object->userID])
            );
            $title = WCF::getLanguage()->get('wcf.user.button.follow');
            $icon = FontAwesomeIcon::fromString('circle-plus;true')->toHtml(24);

            return <<<HTML
                <button
                    type="button"
                    data-following="0"
                    data-follow-user="{$endpoint}"
                    class="userCard__button jsTooltip"
                    title="{$title}"
                >{$icon}</button>
                HTML;
        }
    }
}
