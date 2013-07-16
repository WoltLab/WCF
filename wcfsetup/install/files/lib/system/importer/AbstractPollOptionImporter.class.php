<?php
namespace wcf\system\importer;
use wcf\data\poll\option\PollOptionEditor;

/**
 * Imports poll votes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractPollOptionImporter implements IImporter {
	/**
	 * option object type name
	 * @var string
	 */
	protected $objectTypeName = '';
	
	/**
	 * poll object type name
	 * @var string
	 */
	protected $pollObjectTypeName = '';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['pollID'] = ImportHandler::getInstance()->getNewID($this->pollObjectTypeName, $data['pollID']);
		if (!$data['pollID']) return 0;
		
		$option = PollOptionEditor::create($data);

		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $option->optionID);
		
		return $option->optionID;
	}
}
