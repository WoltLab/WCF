<?php
namespace wcf\system\cache\builder;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\system\WCF;

/**
 * Caches the page data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 * @since       2.2
 */
class PageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'identifier' => [],
			'controller' => [],
			'pages' => [],
			'pageTitles' => [],
			'landingPage' => null
		];
		
		$pageList = new PageList();
		$pageList->readObjects();
		$data['pages'] = $pageList->getObjects();
		
		// get page titles
		$sql = "SELECT  pageID, languageID, title
			FROM    wcf".WCF_N."_page_content";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$pageID = $row['pageID'];
			
			if (!isset($data['pageTitles'])) {
				$data['pageTitles'][$pageID] = [];
			}
			
			$data['pageTitles'][$pageID][$row['languageID'] ?: 0] = $row['title'];
		}
		
		// build lookup table
		/** @var Page $page */
		foreach ($pageList as $page) {
			$data['identifier'][$page->identifier] = $page->pageID;
			$data['controller'][$page->controller] = $page->pageID;
			
			if ($page->isLandingPage) {
				$data['landingPage'] = $page;
			}
		}
		
		return $data;
	}
}
