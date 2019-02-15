<?php
namespace wcf\system\cache\runtime;
use wcf\data\media\Media;
use wcf\data\media\MediaList;

/**
 * Runtime cache implementation for shared media.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 * 
 * @method	Media[]         getCachedObjects()
 * @method	Media           getObject($objectID)
 * @method	Media[]         getObjects(array $objectIDs)
 */
class MediaRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = MediaList::class;
}
