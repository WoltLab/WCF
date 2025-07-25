<?php

namespace wcf\system\interaction\user;

use wcf\action\UserIgnoreAction;
use wcf\data\DatabaseObject;
use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserProfileInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\request\LinkHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for the context menu of user profiles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserProfileInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new class(
                'ignore',
                static fn(UserProfile $user) => WCF::getUser()->userID && WCF::getUser()->userID !== $user->userID
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof UserProfile);

                    if (UserProfileHandler::getInstance()->isIgnoredUser($object->userID)) {
                        $endpoint = StringUtil::encodeHTML(
                            LinkHandler::getInstance()->getControllerLink(UserIgnoreAction::class, ['id' => $object->userID])
                        );
                        $title = WCF::getLanguage()->get('wcf.user.button.unignore');

                        return <<<HTML
                            <button
                                type="button"
                                data-ignored="1"
                                data-ignore-user="{$endpoint}"
                                data-type="button"
                            >{$title}</button>
                            HTML;
                    } else {
                        $endpoint = StringUtil::encodeHTML(
                            LinkHandler::getInstance()->getControllerLink(UserIgnoreAction::class, ['id' => $object->userID])
                        );
                        $title = WCF::getLanguage()->get('wcf.user.button.ignore');

                        return <<<HTML
                            <button
                                type="button"
                                data-ignored="0"
                                data-ignore-user="{$endpoint}"
                                data-type="button"
                            >{$title}</button>
                            HTML;
                    }
                }
            },
            new class(
                'homepage',
                static fn(UserProfile $user) => $user->homepage && $user->homepage != 'http://'
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof UserProfile);

                    $attributes = '';
                    $rel = 'nofollow ugc';
                    if (\EXTERNAL_LINK_TARGET_BLANK) {
                        $rel .= ' noopener';
                        $attributes .= ' target="_blank"';
                    }

                    $attributes .= ' rel="' . $rel . '"';

                    return \sprintf(
                        '<a href="%s"%s>%s</a>',
                        StringUtil::encodeHTML($object->homepage),
                        $attributes,
                        WCF::getLanguage()->get('wcf.user.option.homepage')
                    );
                }
            },
            new class(
                'email',
                isAvailableCallback: static function (UserProfile $user): bool {
                    return $user->userID !== WCF::getUser()->userID
                        && (
                            $user->isAccessible('canViewEmailAddress')
                            || WCF::getSession()->getPermission('admin.user.canEditMailAddress')
                        );
                }
            ) extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof UserProfile);

                    return \sprintf(
                        '<a href="mailto:%s">%s</a>',
                        $object->getEncodedEmail(),
                        WCF::getLanguage()->get('wcf.user.button.mail')
                    );
                }
            },
        ]);

        EventHandler::getInstance()->fire(
            new UserProfileInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserProfile::class;
    }
}
