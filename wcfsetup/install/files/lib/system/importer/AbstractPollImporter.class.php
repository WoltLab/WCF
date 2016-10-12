<?php
namespace wcf\system\importer;
use wcf\data\poll\Poll;
use wcf\data\poll\PollEditor;

/**
 * Imports polls.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractPollImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Poll::class;
	
	/**
	 * object type id for poll
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var	string
	 */
	protected $objectTypeName = '';
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$poll = PollEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $poll->pollID);
		
		return $poll->pollID;
	}
}
