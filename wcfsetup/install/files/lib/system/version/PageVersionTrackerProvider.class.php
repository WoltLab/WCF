<?php
namespace wcf\system\version;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageList;
use wcf\data\page\PageVersionTracker;
use wcf\data\IVersionTrackerObject;

/**
 * Version tracker object type provider implementation for pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 */
class PageVersionTrackerProvider extends AbstractVersionTrackerProvider {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';
	
	/**
	 * @inheritDoc
	 */
	public $className = Page::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = PageVersionTracker::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = PageList::class;
	
	/**
	 * @inheritDoc
	 */
	public $permissionCanAccess = 'admin.content.cms.canManagePage';
	
	/**
	 * @inheritDoc
	 */
	public static $defaultProperty = 'content';
	
	/**
	 * @inheritDoc
	 */
	public static $propertyLabels = [
		'content' => 'wcf.acp.page.content',
		'customURL' => 'wcf.acp.page.customURL',
		'metaDescription' => 'wcf.acp.page.metaDescription',
		'metaKeywords' => 'wcf.acp.page.metaKeywords',
		'title' => 'wcf.global.title'
	];
	
	/**
	 * @inheritDoc
	 */
	public static $trackedProperties = ['title', 'content', 'metaDescription', 'metaKeywords', 'customURL'];
	
	/**
	 * @inheritDoc
	 */
	public function getCurrentVersion(IVersionTrackerObject $object) {
		$properties = $this->getTrackedProperties();
		
		/** @var Page $object */
		$payload = [];
		foreach ($object->getPageContents() as $languageID => $pageContent) {
			$payload[$languageID] = [];
			foreach ($properties as $property) {
				$payload[$languageID][$property] = $pageContent->{$property};
			}
		}
		
		return new VersionTrackerEntry(null, [
			'versionID' => 'current',
			'userID' => 0,
			'username' => '',
			'data' => $payload
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTrackedData(IVersionTrackerObject $object) {
		$data = [];
		
		/** @var PageVersionTracker $object */
		foreach ($object->getContent() as $content) {
			$languageID = $content->languageID ?: 0;
			$data[$languageID] = [];
			
			foreach (static::$trackedProperties as $property) {
				$data[$languageID][$property] = $content->{$property};
			}
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isI18n(IVersionTrackerObject $object) {
		/** @var Page $object */
		return $object->isMultilingual == 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function revert(IVersionTrackerObject $object, VersionTrackerEntry $entry) {
		/** @var PageVersionTracker $object */
		
		// build the content data
		$properties = $this->getTrackedProperties();
		$content = [];
		foreach ($object->getPageContents() as $pageContent) {
			$content[$pageContent->languageID ?: 0] = $entry->getPayloadForProperties($properties, $pageContent->languageID ?: 0);
		}
		
		$action = new PageAction([$object->getDecoratedObject()], 'update', ['content' => $content, 'isRevert' => true]);
		$action->executeAction();
	}
}
