<?php
namespace wcf\data\language\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes language category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Category
 * 
 * @method	LanguageCategory		create()
 * @method	LanguageCategoryEditor[]	getObjects()
 * @method	LanguageCategoryEditor		getSingleObject()
 */
class LanguageCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LanguageCategoryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
