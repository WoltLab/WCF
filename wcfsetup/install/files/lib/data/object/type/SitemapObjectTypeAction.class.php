<?php
namespace wcf\data\object\type;
use wcf\data\IToggleAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\registry\RegistryHandler;
use wcf\system\WCF;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * Executes sitemap object type-related actions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
 * @since	3.1
 *
 * @method	ObjectType		create()
 * @method	ObjectTypeEditor[]	getObjects()
 * @method	ObjectTypeEditor	getSingleObject()
 */
class SitemapObjectTypeAction extends ObjectTypeAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ObjectTypeEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['toggle'];
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $objectEditor) {
			$sitemapData = RegistryHandler::getInstance()->get('com.woltlab.wcf', SitemapRebuildWorker::REGISTRY_PREFIX . $objectEditor->objectType);
			$sitemapData = @unserialize($sitemapData);
			
			if (is_array($sitemapData)) {
				$sitemapData['isDisabled'] = $sitemapData['isDisabled'] ? 0 : 1;
			}
			else {
				$sitemapData = [
					'priority' => $objectEditor->priority,
					'changeFreq' => $objectEditor->changeFreq,
					'rebuildTime' => $objectEditor->rebuildTime,
					'isDisabled' => $objectEditor->isDisabled ? 0 : 1,
				];
			}
			
			RegistryHandler::getInstance()->set('com.woltlab.wcf', SitemapRebuildWorker::REGISTRY_PREFIX . $objectEditor->objectType, serialize($sitemapData));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
		
		foreach ($this->getObjects() as $objectEditor) {
			if ($objectEditor->definitionID != ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.sitemap.object')->definitionID) {
				throw new IllegalLinkException();
			}
		}
	}
}
