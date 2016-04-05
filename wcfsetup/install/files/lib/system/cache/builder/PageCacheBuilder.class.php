<?php
namespace wcf\system\cache\builder;
use wcf\data\page\Page;
use wcf\data\page\PageList;

/**
 * Caches the page data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
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
			'landingPage' => null
		];
		
		$pageList = new PageList();
		$pageList->readObjects();
		$data['pages'] = $pageList->getObjects();
		
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
