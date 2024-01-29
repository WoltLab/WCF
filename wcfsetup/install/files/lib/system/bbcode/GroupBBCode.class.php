<?php

namespace wcf\system\bbcode;

use wcf\data\user\group\UserGroup;
use wcf\system\WCF;

/**
 * Parses the [group] bbcode tag.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
final class GroupBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $content = $openingTag['attributes'][0];
        if (!\str_starts_with($content, '@')) {
            $content = "@{$content}";
        }

        $groupID = (!empty($openingTag['attributes'][1])) ? \intval($openingTag['attributes'][1]) : 0;
        $group = UserGroup::getGroupByID($groupID);
        if ($group === null || !$group->canBeMentioned()) {
            return $content;
        }

        return WCF::getTPL()->fetch('shared_bbcode_group', 'wcf', [
            'group' => $group,
            'groupName' => $content,
        ], true);
    }
}
