<?php
namespace wcf\data\object\type;
use wcf\data\IToggleAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

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
			$objectEditor->update([
				'additionalData' => serialize(array_merge($objectEditor->additionalData, ['isDisabled' => !$objectEditor->isDisabled ? 1 : 0]))
			]);
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
