<?php
namespace wcf\data\option\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes option category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option.category
 * @category	Community Framework
 */
class OptionCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = OptionCategoryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
