<?php
namespace wcf\data\page;
use wcf\data\box\Box;
use wcf\data\page\content\PageContent;
use wcf\data\page\content\PageContentEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Executes page related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 * 
 * @method	PageEditor[]	getObjects()
 * @method	PageEditor	getSingleObject()
 */
class PageAction extends AbstractDatabaseObjectAction implements ISearchAction, ISortableAction, IToggleAction {
	use TDatabaseObjectToggle;
	
	/**
	 * @inheritDoc
	 */
	protected $className = PageEditor::class;
	
	/**
	 * @var	PageEditor
	 */
	protected $pageEditor;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.cms.canManagePage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.cms.canManagePage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.cms.canManagePage'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'getSearchResultList', 'resetPosition', 'toggle', 'update', 'updatePosition'];
	
	/**
	 * @inheritDoc
	 * @return	Page
	 */
	public function create() {
		/** @var Page $page */
		$page = parent::create();
		
		// save page content
		if (!empty($this->parameters['content'])) {
			foreach ($this->parameters['content'] as $languageID => $content) {
				if (!empty($content['htmlInputProcessor'])) {
					/** @noinspection PhpUndefinedMethodInspection */
					$content['content'] = $content['htmlInputProcessor']->getHtml();
				}
				
				/** @var PageContent $pageContent */
				$pageContent = PageContentEditor::create([
					'pageID' => $page->pageID,
					'languageID' => $languageID ?: null,
					'title' => $content['title'],
					'content' => $content['content'],
					'metaDescription' => $content['metaDescription'],
					'metaKeywords' => $content['metaKeywords'],
					'customURL' => $content['customURL']
				]);
				$pageContentEditor = new PageContentEditor($pageContent);
				
				// update search index
				if ($page->pageType == 'text' || $page->pageType == 'html') {
					SearchIndexManager::getInstance()->set(
						'com.woltlab.wcf.page',
						$pageContent->pageContentID,
						$pageContent->content,
						$pageContent->title,
						0,
						null,
						'',
						$languageID ?: null
					);
				}
				
				// save embedded objects
				if (!empty($content['htmlInputProcessor'])) {
					/** @noinspection PhpUndefinedMethodInspection */
					$content['htmlInputProcessor']->setObjectID($pageContent->pageContentID);
					if (MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
						$pageContentEditor->update(['hasEmbeddedObjects' => 1]);
					}
				}
				else if ($page->pageType == 'html' || $page->pageType == 'tpl') {
					HtmlSimpleParser::getInstance()->parse('com.woltlab.wcf.page.content', $pageContent->pageContentID, $pageContent->content);
				}
			}
		}
		
		// save box to page assignments
		if (!empty($this->parameters['boxToPage'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_box_to_page
						(boxID, pageID, visible)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->parameters['boxToPage'] as $boxData) {
				$statement->execute([
					$boxData['boxID'],
					$page->pageID,
					$boxData['visible']
				]);
			}
		}
		
		// save template
		if ($page->pageType == 'tpl') {
			if (!empty($this->parameters['content'])) {
				$pageEditor = new PageEditor($page);
				foreach ($this->parameters['content'] as $languageID => $content) {
					$pageEditor->updateTemplate($languageID ?: null, $content['content']);
				}
			}
		}
		
		return $page;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		// update page content
		if (!empty($this->parameters['content'])) {
			foreach ($this->getObjects() as $page) {
				$versionData = [];
				$hasChanges = false;
				
				foreach ($this->parameters['content'] as $languageID => $content) {
					if (!empty($content['htmlInputProcessor'])) {
						/** @noinspection PhpUndefinedMethodInspection */
						$content['content'] = $content['htmlInputProcessor']->getHtml();
					}
					
					$pageContent = PageContent::getPageContent($page->pageID, ($languageID ?: null));
					$pageContentEditor = null;
					if ($pageContent !== null) {
						// update
						$pageContentEditor = new PageContentEditor($pageContent);
						$pageContentEditor->update([
							'title' => $content['title'],
							'content' => $content['content'],
							'metaDescription' => $content['metaDescription'],
							'metaKeywords' => $content['metaKeywords'],
							'customURL' => $content['customURL']
						]);
						
						$versionData[] = $pageContent;
						foreach (['title', 'content', 'metaDescription', 'metaKeywords', 'customURL'] as $property) {
							if ($pageContent->{$property} != $content[$property]) {
								$hasChanges = true;
								break;
							}
						}
						
						$pageContent = PageContent::getPageContent($page->pageID, ($languageID ?: null));
					}
					else {
						/** @var PageContent $pageContent */
						$pageContent = PageContentEditor::create([
							'pageID' => $page->pageID,
							'languageID' => $languageID ?: null,
							'title' => $content['title'],
							'content' => $content['content'],
							'metaDescription' => $content['metaDescription'],
							'metaKeywords' => $content['metaKeywords'],
							'customURL' => $content['customURL']
						]);
						$pageContentEditor = new PageContentEditor($pageContent);
						
						$versionData[] = $pageContent;
						$hasChanges = true;
					}
					
					// update search index
					if ($page->pageType == 'text' || $page->pageType == 'html') {
						SearchIndexManager::getInstance()->set(
							'com.woltlab.wcf.page',
							$pageContent->pageContentID,
							isset($content['content']) ? $content['content'] : $pageContent->content,
							isset($content['title']) ? $content['title'] : $pageContent->title,
							0,
							null,
							'',
							$languageID ?: null
						);
					}
					
					// save embedded objects
					if (!empty($content['htmlInputProcessor'])) {
						/** @noinspection PhpUndefinedMethodInspection */
						$content['htmlInputProcessor']->setObjectID($pageContent->pageContentID);
						if ($pageContent->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
							$pageContentEditor->update(['hasEmbeddedObjects' => $pageContent->hasEmbeddedObjects ? 0 : 1]);
						}
					}
					else if ($page->pageType == 'html' || $page->pageType == 'tpl') {
						HtmlSimpleParser::getInstance()->parse('com.woltlab.wcf.page.content', $pageContent->pageContentID, $pageContent->content);
					}
				}
				
				// save template
				if ($page->pageType == 'tpl') {
					foreach ($this->parameters['content'] as $languageID => $content) {
						$page->updateTemplate($languageID ?: null, $content['content']);
					}
				}
				
				if ($hasChanges) {
					$pageObj = new PageVersionTracker($page->getDecoratedObject());
					$pageObj->setContent($versionData);
					VersionTracker::getInstance()->add('com.woltlab.wcf.page', $pageObj);
				}
			}
		}
		
		// save box to page assignments
		if (isset($this->parameters['boxToPage'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_box_to_page
				WHERE		pageID = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_box_to_page
						(boxID, pageID, visible)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->getObjects() as $page) {
				$deleteStatement->execute([$page->pageID]);
				
				if (!empty($this->parameters['boxToPage'])) {
					foreach ($this->parameters['boxToPage'] as $boxData) {
						$insertStatement->execute([
							$boxData['boxID'],
							$page->pageID,
							$boxData['visible']
						]);
					}
				}	
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->getObjects() as $object) {
			if (!$object->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->getObjects() as $object) {
			if (!$object->canDisable()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateGetSearchResultList() {
		$this->pageEditor = $this->getSingleObject();
		if ($this->pageEditor->getHandler() === null || !($this->pageEditor->getHandler() instanceof ILookupPageHandler)) {
			throw new UserInputException('objectIDs');
		}
		
		$this->readString('searchString', false, 'data');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		/** @noinspection PhpUndefinedMethodInspection */
		return $this->pageEditor->getHandler()->lookup($this->parameters['data']['searchString']);
	}
	
	/**
	 * Validates parameters to search for a page by its internal name.
	 */
	public function validateSearch() {
		$this->readString('searchString');
	}
	
	/**
	 * Searches for a page by its internal name.
	 * 
	 * @return      array   list of matching pages
	 */
	public function search() {
		$sql = "SELECT          pageID
			FROM            wcf".WCF_N."_page
			WHERE           name LIKE ?
					AND requireObjectID = ?
			ORDER BY        name";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute([
			'%' . $this->parameters['searchString'] . '%',
			0
		]);
		
		$pages = [];
		while ($pageID = $statement->fetchColumn()) {
			$page = PageCache::getInstance()->getPage($pageID);
			$pages[] = [
				'displayLink' => $page->getDisplayLink(),
				'name' => $page->name,
				'pageID' => $pageID
			];
		}
		
		return $pages;
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$pageContentIDs = [];
		foreach ($this->getObjects() as $page) {
			if ($page->pageType == 'tpl') {
				foreach ($page->getPageContents() as $languageID => $content) {
					$file = WCF_DIR . 'templates/' . $page->getTplName(($languageID ?: null)) . '.tpl';
					if (file_exists($file)) {
						@unlink($file);
					}
				}
			}
			
			foreach ($page->getPageContents() as $pageContent) {
				$pageContentIDs[] = $pageContent->pageContentID;
			}
		}
		
		parent::delete();
		
		if (!empty($this->getObjectIDs())) {
			// delete page comments
			CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.page', $this->getObjectIDs());
		}
		
		if (!empty($pageContentIDs)) {
			// delete entry from search index
			SearchIndexManager::getInstance()->delete('com.woltlab.wcf.page', $pageContentIDs);
			// delete embedded object references
			MessageEmbeddedObjectManager::getInstance()->removeObjects('com.woltlab.wcf.page.content', $pageContentIDs);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManagePage']);
		
		$this->pageEditor = $this->getSingleObject();
		
		if (empty($this->parameters['position']) || !is_array($this->parameters['position'])) {
			throw new UserInputException('position');
		}
		
		$seenBoxIDs = [];
		foreach ($this->parameters['position'] as $position => $boxIDs) {
			// validate each position for both existence and the supplied box ids
			if (!in_array($position, Box::$availablePositions) || !is_array($boxIDs)) {
				throw new UserInputException('position');
			}
			
			foreach ($boxIDs as $boxID) {
				// check for duplicate box ids
				if (in_array($boxID, $seenBoxIDs)) {
					throw new UserInputException('position');
				}
				
				$seenBoxIDs[] = $boxID;
			}
		}
		
		// validates box ids
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("boxID IN (?)", [$seenBoxIDs]);
		
		$sql = "SELECT  boxID
			FROM    wcf".WCF_N."_box
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$validBoxIDs = [];
		while ($boxID = $statement->fetchColumn()) {
			$validBoxIDs[] = $boxID;
		}
		
		foreach ($seenBoxIDs as $boxID) {
			if (!in_array($boxID, $validBoxIDs)) {
				throw new UserInputException('position');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$pageID = $this->pageEditor->getDecoratedObject()->pageID;
		
		$sql = "DELETE FROM     wcf".WCF_N."_page_box_order
			WHERE           pageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$pageID]);
		
		$sql = "INSERT INTO     wcf".WCF_N."_page_box_order
					(pageID, boxID, showOrder)
			VALUES          (?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['position'] as $boxIDs) {
			for ($i = 0, $length = count($boxIDs); $i < $length; $i++) {
				$statement->execute([
					$pageID,
					$boxIDs[$i],
					$i
				]);
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Validates parameters to reset the custom box positions for provided page.
	 */
	public function validateResetPosition() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManagePage']);
		
		$this->pageEditor = $this->getSingleObject();
	}
	
	/**
	 * Resets the custom box positions for provided page.
	 */
	public function resetPosition() {
		$sql = "DELETE FROM     wcf".WCF_N."_page_box_order
			WHERE           pageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->pageEditor->getDecoratedObject()->pageID]);
	}
}
