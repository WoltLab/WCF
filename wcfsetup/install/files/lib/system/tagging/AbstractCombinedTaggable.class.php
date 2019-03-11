<?php
namespace wcf\system\tagging;
use wcf\data\tag\Tag;

/**
 * Abstract implementation of a taggable with support for searches with multiple tags.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Tagging
 * @since       5.2
 */
abstract class AbstractCombinedTaggable extends AbstractTaggable implements ICombinedTaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectList(Tag $tag) {
		return $this->getObjectListFor([$tag]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectListFor(array $tags) {
		return null;
	}
}
