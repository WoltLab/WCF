<?php
namespace wcf\system\page\handler;
use wcf\data\article\category\ArticleCategory;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Menu page handler for the category article list page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
class CategoryArticleListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return LinkHandler::getInstance()->getLink('CategoryArticleList', [
			'object' => ArticleCategory::getCategory($objectID),
			'forceFrontend' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return ArticleCategory::getCategory($objectID) !== null;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return ArticleCategory::getCategory($objectID)->isAccessible();
	}
	
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('category.objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.woltlab.wcf.article.category')]);
		$conditionBuilder->add('(category.title LIKE ? OR language_item.languageItemValue LIKE ?)', ['%' . $searchString . '%', '%' . $searchString . '%']);
		$sql = "SELECT          DISTINCT categoryID
			FROM            wcf".WCF_N."_category category
			LEFT JOIN       wcf".WCF_N."_language_item language_item
			ON              (language_item.languageItem = category.title)
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql, 10);
		$statement->execute($conditionBuilder->getParameters());
		$results = [];
		while ($categoryID = $statement->fetchColumn()) {
			$category = ArticleCategory::getCategory($categoryID);
			
			// build hierarchy
			$description = '';
			foreach ($category->getParentCategories() as $parentCategory) {
				$description .= $parentCategory->getTitle() . ' &raquo; ';
			}
			
			$results[] = [
				'description' => $description,
				'image' => 'fa-folder-open-o',
				'link' => $category->getLink(),
				'objectID' => $categoryID,
				'title' => $category->getTitle()
			];
		}
		
		return $results;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->pageObjectID === null) {
			return '';
		}
		
		$category = ArticleCategory::getCategory($user->pageObjectID);
		if ($category === null || !$category->isAccessible()) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['category' => $category]);
	}
}
