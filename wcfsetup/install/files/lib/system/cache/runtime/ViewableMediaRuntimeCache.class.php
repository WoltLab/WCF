<?php
namespace wcf\system\cache\runtime;
use wcf\data\media\ViewableMedia;
use wcf\data\media\ViewableMediaList;

/**
 * Runtime cache implementation for viewable media.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 * 
 * @method	ViewableMedia[]         getCachedObjects()
 * @method	ViewableMedia           getObject($objectID)
 * @method	ViewableMedia[]         getObjects(array $objectIDs)
 */
class ViewableMediaRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = ViewableMediaList::class;
}
