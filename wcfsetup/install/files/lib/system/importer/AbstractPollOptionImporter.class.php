<?php
namespace wcf\system\importer;
use wcf\data\poll\option\PollOption;
use wcf\data\poll\option\PollOptionEditor;

/**
 * Imports poll votes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractPollOptionImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = PollOption::class;
	
	/**
	 * option object type name
	 * @var	string
	 */
	protected $objectTypeName = '';
	
	/**
	 * poll object type name
	 * @var	string
	 */
	protected $pollObjectTypeName = '';
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['pollID'] = ImportHandler::getInstance()->getNewID($this->pollObjectTypeName, $data['pollID']);
		if (!$data['pollID']) return 0;
		
		$option = PollOptionEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $option->optionID);
		
		return $option->optionID;
	}
}
