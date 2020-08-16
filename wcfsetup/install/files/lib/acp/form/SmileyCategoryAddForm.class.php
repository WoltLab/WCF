<?php
namespace wcf\acp\form;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the smiley category add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 */
class SmileyCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.bbcode.smiley';
	
	/**
	 * @inheritDoc
	 */
	public $pageTitle = 'wcf.acp.smiley.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_SMILEY'];
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getTPL()->assign([
			'objectEditLink' => LinkHandler::getInstance()->getLink('SmileyCategoryEdit', ['id' => $this->objectAction->getReturnValues()['returnValues']->categoryID]),
		]);
	}
}
