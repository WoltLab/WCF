<?php
namespace wcf\system\tagging;
use wcf\data\DatabaseObjectList;
use wcf\data\tag\Tag;

/**
 * Extended interface for taggable objects that support searches for objects
 * that match multiple tags.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Tagging
 * @since       5.2
 */
interface ICombinedTaggable extends ITaggable {
	/**
	 * Returns a list of tagged objects that match all provided tags.
	 *
	 * @param Tag[] $tags
	 * @return DatabaseObjectList
	 * @since	5.2
	 */
	public function getObjectListFor(array $tags);
}
