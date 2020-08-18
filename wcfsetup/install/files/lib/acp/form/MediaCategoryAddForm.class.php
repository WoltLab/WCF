<?php
namespace wcf\acp\form;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the media category add form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class MediaCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.media.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.media.category';
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getTPL()->assign([
			'objectEditLink' => LinkHandler::getInstance()->getControllerLink(MediaCategoryEditForm::class, ['id' => $this->objectAction->getReturnValues()['returnValues']->categoryID]),
		]);
	}
}
