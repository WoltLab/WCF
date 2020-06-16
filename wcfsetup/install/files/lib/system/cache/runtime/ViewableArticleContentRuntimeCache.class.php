<?php
namespace wcf\system\cache\runtime;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\content\ViewableArticleContentList;

/**
 * Runtime cache implementation for viewable article contents.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Runtime
 * @since	5.2
 *
 * @method	ViewableArticleContent[]		getCachedObjects()
 * @method	ViewableArticleContent		        getObject($objectID)
 * @method	ViewableArticleContent[]		getObjects(array $objectIDs)
 */
class ViewableArticleContentRuntimeCache extends AbstractRuntimeCache {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = ViewableArticleContentList::class;
}
