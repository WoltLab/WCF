<?php
namespace wcf\system\worker;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for database table encoding conversion.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class DatabaseConvertEncodingWorker extends AbstractWorker {
	/**
	 * @inheritDoc
	 */
	protected $limit = 1;
	
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		if ($this->count === null) {
			$this->count = count($this->getTables());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('RebuildData');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		$tables = $this->getTables();
		
		$statement = WCF::getDB()->prepareStatement("SET FOREIGN_KEY_CHECKS=0");
		$statement->execute();
		
		$convertTables = array_slice($tables, $this->limit * $this->loopCount, $this->limit);
		foreach ($convertTables as $table) {
			$sql = "ALTER TABLE " . $table . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			$sql = "ALTER TABLE " . $table . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		$statement = WCF::getDB()->prepareStatement("SET FOREIGN_KEY_CHECKS=1");
		$statement->execute();
	}
	
	/**
	 * Returns the list of known database tables.
	 * 
	 * @return      string[]
	 */
	protected function getTables() {
		$sql = "SELECT  DISTINCT sqlTable
			FROM    wcf".WCF_N."_package_installation_sql_log";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$tables = [];
		while ($table = $statement->fetchColumn()) {
			$tables[] = $table;
		}
		
		return $tables;
	}
}
