<?php
namespace wcf\system\version;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxList;
use wcf\data\box\BoxVersionTracker;
use wcf\data\IVersionTrackerObject;

/**
 * Version tracker object type provider implementation for boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 */
class BoxVersionTrackerProvider extends AbstractVersionTrackerProvider {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.list';
	
	/**
	 * @inheritDoc
	 */
	public $className = Box::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = BoxVersionTracker::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = BoxList::class;
	
	/**
	 * @inheritDoc
	 */
	public $permissionCanAccess = 'admin.content.cms.canManageBox';
	
	/**
	 * @inheritDoc
	 */
	public static $defaultProperty = 'content';
	
	/**
	 * @inheritDoc
	 */
	public static $propertyLabels = [
		'content' => 'wcf.acp.box.content',
		'title' => 'wcf.global.title'
	];
	
	/**
	 * @inheritDoc
	 */
	public static $trackedProperties = ['title', 'content'];
	
	/**
	 * @inheritDoc
	 */
	public function getCurrentVersion(IVersionTrackerObject $object) {
		$properties = $this->getTrackedProperties();
		
		/** @var Box $object */
		$payload = [];
		foreach ($object->getBoxContents() as $languageID => $boxContent) {
			$payload[$languageID] = [];
			foreach ($properties as $property) {
				$payload[$languageID][$property] = $boxContent->{$property};
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
		
		/** @var BoxVersionTracker $object */
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
		/** @var Box $object */
		return $object->isMultilingual == 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function revert(IVersionTrackerObject $object, VersionTrackerEntry $entry) {
		/** @var BoxVersionTracker $object */
		
		// build the content data
		$properties = $this->getTrackedProperties();
		$content = [];
		foreach ($object->getBoxContents() as $boxContent) {
			$content[$boxContent->languageID ?: 0] = $entry->getPayloadForProperties($properties, $boxContent->languageID ?: 0);
		}
		
		$action = new BoxAction([$object->getDecoratedObject()], 'update', ['content' => $content, 'isRevert' => true]);
		$action->executeAction();
	}
}
