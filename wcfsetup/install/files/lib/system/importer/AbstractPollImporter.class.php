<?php
namespace wcf\system\importer;
use wcf\data\poll\PollEditor;

/**
 * Imports polls.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractPollImporter implements IImporter {
	/**
	 * object type id for poll
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * object type name
	 * @var string
	 */
	protected $objectTypeName = '';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$poll = PollEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		
		ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $poll->pollID);
		
		return $poll->pollID;
	}
}
