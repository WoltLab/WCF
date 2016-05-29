<?php
namespace wcf\system\cache\runtime;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;

/**
 * Runtime cache implementation for comments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.runtime
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Comment[]	getCachedObjects()
 * @method	Comment		getObject($objectID)
 * @method	Comment[]	getObjects(array $objectIDs)
 */
class CommentRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = CommentList::class;
}
