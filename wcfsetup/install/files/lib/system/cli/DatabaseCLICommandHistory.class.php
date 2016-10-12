<?php
namespace wcf\system\cli;
use phpline\console\history\MemoryHistory;
use wcf\system\WCF;

/**
 * A phpline history that saves the items in database.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
class DatabaseCLICommandHistory extends MemoryHistory {
	/**
	 * should the history automatically be saved
	 * @var	boolean
	 */
	public $autoSave = true;
	
	/**
	 * Saves the history.
	 * 
	 * @param	boolean		$append
	 */
	public function save($append = false) {
		if (!$append) {
			$sql = "DELETE FROM	wcf".WCF_N."_cli_history
				WHERE		userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([WCF::getUser()->userID]);
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_cli_history (userID, command)
			VALUES (?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		
		foreach ($this as $item) {
			$statement->execute([WCF::getUser()->userID, $item]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Loads the history.
	 */
	public function load() {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_cli_history
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([WCF::getUser()->userID]);
		
		while ($row = $statement->fetchArray()) {
			$this->add($row['command']);
		}
		
		$this->moveToEnd();
	}
	
	/**
	 * Automatically saves the history if $autoSave is set to true.
	 */
	public function __destruct() {
		if ($this->autoSave) {
			$this->save();
		}
	}
}
