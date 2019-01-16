<?php
namespace wcf\system\bbcode;
use wcf\data\user\group\UserGroup;
use wcf\system\WCF;

/**
 * Parses the [user] bbcode tag.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Bbcode
 * @since       5.0
 */
class GroupBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$groupID = (!empty($openingTag['attributes'][0])) ? intval($openingTag['attributes'][0]) : 0;
		$group = UserGroup::getGroupByID($groupID);
		if ($group === null || !$group->canBeMentioned()) {
			return "[group]{$content}[/group]";
		}
		
		return WCF::getTPL()->fetch('groupBBCodeTag', 'wcf', [
			'group' => $group,
			'groupName' => $content,
		], true);
	}
}
