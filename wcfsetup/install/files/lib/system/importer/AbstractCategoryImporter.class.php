<?php
namespace wcf\system\importer;
use wcf\data\category\CategoryAction;

/**
 * Imports categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractCategoryImporter implements IImporter {
	/**
	 * object type id for categories
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var integer
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		if (!empty($data['parentCategoryID'])) $data['parentCategoryID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['parentCategoryID']);
		
		$action = new CategoryAction(array(), 'create', array(
			'data' => array_merge($data, array('objectTypeID' => $this->objectTypeID))		
		));
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->categoryID;
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $newID);
		
		return $newID;
	}
}
