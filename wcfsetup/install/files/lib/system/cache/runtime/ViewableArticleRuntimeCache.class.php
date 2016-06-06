<?php
namespace wcf\system\cache\runtime;
use wcf\data\article\ViewableArticle;
use wcf\data\article\ViewableArticleList;

/**
 * Runtime cache implementation for viewable articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.runtime
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	ViewableArticle[]		getCachedObjects()
 * @method	ViewableArticle		        getObject($objectID)
 * @method	ViewableArticle[]		getObjects(array $objectIDs)
 */
class ViewableArticleRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = ViewableArticleList::class;
}
