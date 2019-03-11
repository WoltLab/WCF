<?php
namespace wcf\acp\form;

/**
 * Shows the media category edit form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class MediaCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.media.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.media.category';
}
