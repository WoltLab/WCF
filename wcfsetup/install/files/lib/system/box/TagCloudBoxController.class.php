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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 */
class TagCloudBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * taggable object type
	 * @var string
	 */
	protected $objectType = '';
	
	/**
	 * needed permission to view this box
	 * @var string
	 */
	protected $neededPermission = '';
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag') && (!$this->neededPermission || WCF::getSession()->getPermission($this->neededPermission))) {
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
				WCF::getTPL()->assign([
					'tags' => $tags,
					'taggableObjectType' => $this->objectType
				]);
				
				$this->content = WCF::getTPL()->fetch('tagCloudBox');
			}
		}
	}
}
