<?php
namespace wcf\system\worker;
use wcf\data\page\content\PageContentEditor;
use wcf\data\page\content\PageContentList;
use wcf\data\page\PageList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;

/**
 * Worker implementation for updating cms pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @since	3.1
 * 
 * @method	PageList	getObjectList()
 */
class PageRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = PageList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'page.pageID';
		$this->objectList->getConditionBuilder()->add('page.pageType <> ?', ['system']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset search index
			SearchIndexManager::getInstance()->reset('com.woltlab.wcf.page');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		$pages = $this->objectList->getObjects();
		
		// update page content
		$pageContentList = new PageContentList();
		$pageContentList->getConditionBuilder()->add('page_content.pageID IN (?)', [$this->objectList->getObjectIDs()]);
		$pageContentList->readObjects();
		foreach ($pageContentList as $pageContent) {
			$page = $pages[$pageContent->pageID];
			if ($page->pageType == 'text' || $page->pageType == 'html') {
				// update search index
				SearchIndexManager::getInstance()->set(
					'com.woltlab.wcf.page',
					$pageContent->pageContentID,
					$pageContent->content,
					$pageContent->title,
					0,
					null,
					'',
					$pageContent->languageID
				);
			}
			
			// update embedded objects
			$this->getHtmlInputProcessor()->processEmbeddedContent($pageContent->content, 'com.woltlab.wcf.page.content', $pageContent->pageContentID);
			
			$hasEmbeddedObjects = 0;
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
				$hasEmbeddedObjects = 1;
			}
			
			if ($hasEmbeddedObjects != $pageContent->hasEmbeddedObjects) {
				$pageContentEditor = new PageContentEditor($pageContent);
				$pageContentEditor->update(['hasEmbeddedObjects' => $hasEmbeddedObjects]);
			}
		}
	}
	
	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}
		
		return $this->htmlInputProcessor;
	}
}
