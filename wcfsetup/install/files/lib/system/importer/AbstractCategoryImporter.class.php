<?php
namespace wcf\system\importer;
use wcf\data\category\CategoryEditor;

/**
 * Imports categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCategoryImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\category\Category';
	
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
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		if (!empty($data['parentCategoryID'])) $data['parentCategoryID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['parentCategoryID']);
		
		$category = CategoryEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $category->categoryID);
		
		return $category->categoryID;
	}
}
