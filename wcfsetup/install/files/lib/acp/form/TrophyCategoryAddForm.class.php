<?php
namespace wcf\acp\form;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the trophy category add form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class TrophyCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.trophy.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.trophy.category';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TROPHY'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.trophy.canManageTrophy'];
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getTPL()->assign([
			'objectEditLink' => LinkHandler::getInstance()->getLink('TrophyCategoryEdit', ['id' => $this->objectAction->getReturnValues()['returnValues']->categoryID]),
		]);
	}
}
