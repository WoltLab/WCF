<?php

namespace wcf\system\interaction\user;

use wcf\action\UserIgnoreAction;
use wcf\data\DatabaseObject;
use wcf\data\user\UserProfile;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user ignore interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class UserIgnoreInteraction extends AbstractInteraction
{
    public function __construct()
    {
        parent::__construct('ignore');
    }

    #[\Override]
    public function isAvailable(DatabaseObject $object): bool
    {
        \assert($object instanceof UserProfile);

        if (!WCF::getUser()->userID || WCF::getUser()->userID === $object->userID) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        \assert($object instanceof UserProfile);

        if (UserProfileHandler::getInstance()->isIgnoredUser($object->userID)) {
            $endpoint = StringUtil::encodeHTML(
                LinkHandler::getInstance()->getControllerLink(UserIgnoreAction::class, ['id' => $object->userID])
            );
            $title = WCF::getLanguage()->get('wcf.user.button.unignore');
            $icon = FontAwesomeIcon::fromString('eye;true')->toHtml(24);

            return <<<HTML
                <button
                    type="button"
                    data-ignored="1"
                    data-ignore-user="{$endpoint}"
                    class="userCard__button jsTooltip"
                    title="{$title}"
                >{$icon}</button>
                HTML;
        } else {
            $endpoint = StringUtil::encodeHTML(
                LinkHandler::getInstance()->getControllerLink(UserIgnoreAction::class, ['id' => $object->userID])
            );
            $title = WCF::getLanguage()->get('wcf.user.button.ignore');
            $icon = FontAwesomeIcon::fromString('eye-slash;true')->toHtml(24);

            return <<<HTML
                <button
                    type="button"
                    data-ignored="0"
                    data-ignore-user="{$endpoint}"
                    class="userCard__button jsTooltip"
                    title="{$title}"
                >{$icon}</button>
                HTML;
        }
    }
}
