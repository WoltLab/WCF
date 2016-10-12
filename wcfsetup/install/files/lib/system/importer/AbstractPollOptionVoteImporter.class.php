<?php
namespace wcf\system\importer;
use wcf\system\WCF;

/**
 * Imports poll votes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractPollOptionVoteImporter extends AbstractImporter {
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
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		$data['pollID'] = ImportHandler::getInstance()->getNewID($this->pollObjectTypeName, $data['pollID']);
		if (!$data['pollID']) return 0;
		
		$data['optionID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['optionID']);
		if (!$data['optionID']) return 0;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_poll_option_vote
						(pollID, optionID, userID)
			VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$data['pollID'], $data['optionID'], $data['userID']]);
		
		return 1;
	}
}
