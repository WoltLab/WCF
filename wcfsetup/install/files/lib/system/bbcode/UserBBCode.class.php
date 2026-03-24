<?php

namespace wcf\system\bbcode;

use wcf\data\user\UserProfile;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Parses the [user] bbcode tag.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
final class UserBBCode extends AbstractBBCode
{
    #[\Override]
    public function getParsedTag(array $openingTag, string $content, array $closingTag, BBCodeParser $parser): string
    {
        $content = $openingTag['attributes'][0];
        if (!\str_starts_with($content, '@')) {
            $content = "@{$content}";
        }

        $userID = (!empty($openingTag['attributes'][1])) ? \intval($openingTag['attributes'][1]) : 0;
        if (!$userID) {
            return $content;
        }

        /** @var UserProfile $userProfile */
        $userProfile = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.user', $userID);

        return WCF::getTPL()->render('wcf', 'shared_bbcode_user', [
            'username' => $content,
            'userProfile' => $userProfile,
        ]);
    }
}
