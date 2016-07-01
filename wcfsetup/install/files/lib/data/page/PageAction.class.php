<?php
namespace wcf\data\page;
use wcf\data\page\content\PageContent;
use wcf\data\page\content\PageContentEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\WCF;

/**
 * Executes page related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 * 
 * @method	PageEditor[]	getObjects()
 * @method	PageEditor	getSingleObject()
 */
class PageAction extends AbstractDatabaseObjectAction implements ISearchAction, IToggleAction {
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
	protected $requireACP = ['create', 'delete', 'toggle', 'update'];
	
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
					'languageID' => ($languageID ?: null),
					'title' => $content['title'],
					'content' => $content['content'],
					'metaDescription' => $content['metaDescription'],
					'metaKeywords' => $content['metaKeywords'],
					'customURL' => $content['customURL']
				]);
				$pageContentEditor = new PageContentEditor($pageContent);
				
				// save embedded objects
				if (!empty($content['htmlInputProcessor'])) {
					/** @noinspection PhpUndefinedMethodInspection */
					$content['htmlInputProcessor']->setObjectID($pageContent->pageContentID);
					if (MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
						$pageContentEditor->update(['hasEmbeddedObjects' => 1]);
					}
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
				foreach ($this->parameters['content'] as $languageID => $content) {
					file_put_contents(WCF_DIR . 'templates/' . $page->getTplName(($languageID ?: null)) . '.tpl', $content['content']);
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
					}
					else {
						/** @var PageContent $pageContent */
						$pageContent = PageContentEditor::create([
							'pageID' => $page->pageID,
							'languageID' => ($languageID ?: null),
							'title' => $content['title'],
							'content' => $content['content'],
							'metaDescription' => $content['metaDescription'],
							'metaKeywords' => $content['metaKeywords'],
							'customURL' => $content['customURL']
						]);
						$pageContentEditor = new PageContentEditor($pageContent);
					}
					
					// save embedded objects
					if (!empty($content['htmlInputProcessor'])) {
						/** @noinspection PhpUndefinedMethodInspection */
						$content['htmlInputProcessor']->setObjectID($pageContent->pageContentID);
						if ($pageContent->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
							$pageContentEditor->update(['hasEmbeddedObjects' => ($pageContent->hasEmbeddedObjects ? 0 : 1)]);
						}
					}
				}
				
				// save template
				if ($page->pageType == 'tpl') {
					foreach ($this->parameters['content'] as $languageID => $content) {
						file_put_contents(WCF_DIR . 'templates/' . $page->getTplName(($languageID ?: null)) . '.tpl', $content['content']);
					}
				}
			}
		}
		
		// save box to page assignments
		if (!empty($this->parameters['boxToPage'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_box_to_page
				WHERE		pageID = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_box_to_page
						(boxID, pageID, visible)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->getObjects() as $page) {
				$deleteStatement->execute([$page->pageID]);
				
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
	public function toggle() {
		foreach ($this->getObjects() as $object) {
			$object->update(['isDisabled' => ($object->isDisabled) ? 0 : 1]);
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
	 * @inheritDoc
	 */
	public function delete() {
		foreach ($this->getObjects() as $page) {
			if ($page->pageType == 'tpl') {
				foreach ($page->getPageContents() as $languageID => $content) {
					$file = WCF_DIR . 'templates/' . $page->getTplName(($languageID ?: null)) . '.tpl';
					if (file_exists($file)) {
						@unlink($file);
					}
				}
			}
		}
		
		parent::delete();
	}
}
