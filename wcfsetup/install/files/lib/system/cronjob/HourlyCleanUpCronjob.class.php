<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;

/**
 * Cronjob for a hourly system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class HourlyCleanUpCronjob extends AbstractCronjob {
	/**
	 * @see wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// TODO
	}
}
