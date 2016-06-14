<?php
namespace wcf\system\importer;
use wcf\data\label\Label;
use wcf\data\label\LabelEditor;

/**
 * Imports labels.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class LabelImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Label::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.label.group', $data['groupID']);
		if (!$data['groupID']) return 0;
		
		$label = LabelEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.label', $oldID, $label->labelID);
		
		return $label->labelID;
	}
}
