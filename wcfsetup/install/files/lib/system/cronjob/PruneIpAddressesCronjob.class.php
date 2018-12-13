<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Prunes old ip addresses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class PruneIpAddressesCronjob extends AbstractCronjob {
	/**
	 * list of columns grouped by the corresponding table
	 * @var string[][]
	 */
	public $columns = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		if (!PRUNE_IP_ADDRESS) return;
		
		$this->columns['wcf'.WCF_N.'_user'][] = 'registrationIpAddress';
		
		parent::execute($cronjob);
		
		foreach ($this->columns as $tableName => $columns) {
			$columnUpdate = '';
			foreach ($columns as $column) {
				if (!empty($columnUpdate)) $columnUpdate .= ',';
				$columnUpdate .= "{$column} = ''";
			}
			
			$sql = "UPDATE  {$tableName}
				SET     {$columnUpdate}";
			WCF::getDB()
				->prepareStatement($sql)
				->execute();
		}
	}
}
