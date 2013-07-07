<?php
namespace wcf\system\importer;
use wcf\data\label\LabelEditor;
use wcf\system\WCF;

/**
 * Imports labels.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class LabelImporter implements IImporter {
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$label = LabelEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.label', $oldID, $label->labelID);
		
		return $label->labelID;
	}
}
