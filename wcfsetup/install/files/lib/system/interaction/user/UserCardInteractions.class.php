<?php

namespace wcf\system\interaction\user;

use wcf\data\DatabaseObject;
use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserCardInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for the context menu of user cards.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserCardInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
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
                static fn(UserProfile $user) => $user->userID != WCF::getUser()->userID && $user->isAccessible('canViewEmailAddress')
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
            new UserCardInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserProfile::class;
    }
}
