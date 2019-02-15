<?php
namespace wcf\system\worker;
use wcf\data\page\content\PageContentEditor;
use wcf\data\page\content\PageContentList;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;

/**
 * Worker implementation for updating cms pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
			/** @var Page $page */
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
			$data = [];
			if ($page->pageType == 'text') {
				$this->getHtmlInputProcessor()->reprocess($pageContent->content, 'com.woltlab.wcf.page.content', $pageContent->pageContentID);
				$data['content'] = $this->getHtmlInputProcessor()->getHtml();
				
				$hasEmbeddedObjects = 0;
				if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
					$hasEmbeddedObjects = 1;
				}
				
				if ($hasEmbeddedObjects != $pageContent->hasEmbeddedObjects) {
					$data['hasEmbeddedObjects'] = $hasEmbeddedObjects;
				}
			}
			else if ($page->pageType == 'html' || $page->pageType == 'tpl') {
				HtmlSimpleParser::getInstance()->parse('com.woltlab.wcf.page.content', $pageContent->pageContentID, $pageContent->content);
			}
			
			if (!empty($data)) {
				$pageContentEditor = new PageContentEditor($pageContent);
				$pageContentEditor->update($data);
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
