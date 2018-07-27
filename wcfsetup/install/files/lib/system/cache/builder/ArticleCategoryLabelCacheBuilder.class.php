<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the available label group ids for article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 * @since       3.1
 */
class ArticleCategoryLabelCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.objectType', 'com.woltlab.wcf.article.category')->objectTypeID]);
		$conditionBuilder->add('objectID IN (SELECT categoryID FROM wcf'.WCF_N.'_category WHERE objectTypeID = ?)', [CategoryHandler::getInstance()->getObjectTypeByName('com.woltlab.wcf.article.category')->objectTypeID]);
		
		$sql = "SELECT	groupID, objectID
			FROM	wcf".WCF_N."_label_group_to_object
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		return $statement->fetchMap('objectID', 'groupID', false);
	}
}
