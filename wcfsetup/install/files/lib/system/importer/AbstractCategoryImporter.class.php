<?php
namespace wcf\system\importer;
use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;

/**
 * Imports categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractCategoryImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Category::class;
	
	/**
	 * object type id for categories
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var	integer
	 */
	protected $objectTypeName = '';
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if (!empty($data['parentCategoryID'])) $data['parentCategoryID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['parentCategoryID']);
		
		$category = CategoryEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $category->categoryID);
		
		return $category->categoryID;
	}
}
