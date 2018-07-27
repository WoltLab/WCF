<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\content\SearchResultPageContent;
use wcf\data\page\content\SearchResultPageContentList;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in cms pages.
 *
 * @author      Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 * @since	3.1
 */
class PageSearch extends AbstractSearchableObjectType {
	/**
	 * message data cache
	 * @var	SearchResultPageContent[]
	 */
	public $messageCache = [];
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs, array $additionalData = null) {
		$list = new SearchResultPageContentList();
		$list->setObjectIDs($objectIDs);
		$list->readObjects();
		foreach ($list->getObjects() as $content) {
			$this->messageCache[$content->pageContentID] = $content;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObject($objectID) {
		if (isset($this->messageCache[$objectID])) {
			return $this->messageCache[$objectID];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTableName() {
		return 'wcf'.WCF_N.'_page_content';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIDFieldName() {
		return $this->getTableName().'.pageContentID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubjectFieldName() {
		return $this->getTableName().'.title';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsernameFieldName() {
		return "''";
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTimeFieldName() {
		return 'wcf'.WCF_N.'_page_content.pageContentID';
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getConditions(IForm $form = null) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('wcf'.WCF_N.'_page.pageType IN (?) AND wcf'.WCF_N.'_page.isDisabled = ?', [['text', 'html'], 0]);
		
		// acl
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.acl.simple', 'com.woltlab.wcf.page');
		$conditionBuilder->add('(	
			wcf'.WCF_N.'_page_content.pageID NOT IN (
				SELECT objectID FROM wcf' . WCF_N . '_acl_simple_to_group WHERE objectTypeID = ?
				UNION
				SELECT objectID FROM wcf' . WCF_N . '_acl_simple_to_user WHERE objectTypeID = ?
			)
			OR
			wcf'.WCF_N.'_page_content.pageID IN (
				SELECT objectID FROM wcf' . WCF_N . '_acl_simple_to_group WHERE objectTypeID = ? AND groupID IN (?)
				UNION
				SELECT objectID FROM wcf' . WCF_N . '_acl_simple_to_user WHERE objectTypeID = ? AND userID = ?
			)
		)', [$objectTypeID, $objectTypeID, $objectTypeID, WCF::getUser()->getGroupIDs(), $objectTypeID, WCF::getUser()->userID]);
		
		return $conditionBuilder;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getJoins() {
		return 'INNER JOIN wcf'.WCF_N.'_page ON (wcf'.WCF_N.'_page.pageID = '.$this->getTableName().'.pageID)';
	}
}
