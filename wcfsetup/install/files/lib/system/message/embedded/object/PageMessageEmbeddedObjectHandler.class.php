<?php
namespace wcf\system\message\embedded\object;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Parses embedded pages and outputs their link or title.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
class PageMessageEmbeddedObjectHandler extends AbstractSimpleMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		$pageIDs = [];
		if (!empty($embeddedData['wsp'])) {
			for ($i = 0, $length = count($embeddedData['wsp']); $i < $length; $i++) {
				$pageIDs[] = intval($embeddedData['wsp'][$i][0]);
			}
		}
		
		return array_unique($pageIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$pages = [];
		
		foreach ($objectIDs as $objectID) {
			$page = PageCache::getInstance()->getPage($objectID);
			if ($page !== null) {
				$pages[$objectID] = $page;
			}
		}
		
		return $pages;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateValues($objectType, $objectID, array $values) {
		return array_filter($values, function($value) {
			return (PageCache::getInstance()->getPage($value) !== null);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceSimple($objectType, $objectID, $value, array $attributes) {
		/** @var Page $page */
		$page = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.page', $value);
		if ($page === null) {
			return null;
		}
		
		$return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
		switch ($return) {
			case 'title':
				return $page->getTitle();
				break;
			
			case 'link':
			default:
				return $page->getLink();
				break;
		}
	}
}
