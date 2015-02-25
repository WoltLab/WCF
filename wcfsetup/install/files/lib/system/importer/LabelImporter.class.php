<?php
namespace wcf\system\importer;
use wcf\data\label\LabelEditor;

/**
 * Imports labels.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class LabelImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\label\Label';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.label.group', $data['groupID']);
		if (!$data['groupID']) return 0;
		
		$label = LabelEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.label', $oldID, $label->labelID);
		
		return $label->labelID;
	}
}
