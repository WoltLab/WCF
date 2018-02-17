<?php
namespace wcf\system\box;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagCloud;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;

/**
 * Box for the tag cloud.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 */
class TagCloudBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * taggable object type
	 * @var string
	 */
	protected $objectType = '';
	
	/**
	 * needed permission to view this box
	 * @var string|string[]
	 */
	protected $neededPermission = '';
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
			if ($this->neededPermission) {
				if (is_string($this->neededPermission)) {
					if (!WCF::getSession()->getPermission($this->neededPermission)) {
						return;
					}
				}
				else if (is_array($this->neededPermission)) {
					$hasPermission = false;
					foreach ($this->neededPermission as $permission) {
						if (WCF::getSession()->getPermission($permission)) {
							$hasPermission = true;
							break;
						}
					}
					
					if (!$hasPermission) {
						return;
					}
				}
				else {
					throw new \LogicException("\$neededPermission must not be of type '" . gettype($this->neededPermission) . "', only strings and arrays are supported.");
				}
			}
			
			$languageIDs = [];
			if (LanguageFactory::getInstance()->multilingualismEnabled()) {
				$languageIDs = WCF::getUser()->getLanguageIDs();
			}
			
			if ($this->objectType) {
				$tagCloud = new TypedTagCloud($this->objectType, $languageIDs);
			}
			else {
				$tagCloud = new TagCloud($languageIDs);
			}
			$tags = $tagCloud->getTags();
			
			if (!empty($tags)) {
				$this->content = WCF::getTPL()->fetch('tagCloudBox', 'wcf', [
					'tags' => $tags,
					'taggableObjectType' => $this->objectType
				], true);
			}
		}
	}
}
