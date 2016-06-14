<?php
namespace wcf\system\cache\runtime;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;

/**
 * Runtime cache implementation for comment responses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	3.0
 * 
 * @method	CommentResponse[]	getCachedObjects()
 * @method	CommentResponse		getObject($objectID)
 * @method	CommentResponse[]	getObjects(array $objectIDs)
 */
class CommentResponseRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = CommentResponseList::class;
}
